<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFitnessGoalRequest;
use App\Http\Requests\StoreGymEventRequest;
use App\Http\Requests\StoreMealPlanRequest;
use App\Http\Requests\StoreWorkoutAssignmentRequest;
use App\Models\FitnessGoal;
use App\Models\GymEvent;
use App\Models\MealPlan;
use App\Models\Member;
use App\Models\NotificationDelivery;
use App\Models\WorkoutAssignment;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FitnessController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('fitness.view', $organization, $tenantContext->branch()), 403);

        $user = request()->user();
        $isTrainer = $user->roleAssignments()
            ->where('organization_id', $organization->id)
            ->whereHas('role', fn ($query) => $query->where('key', 'trainer'))
            ->exists();
        $membersQuery = Member::query()->whereBelongsTo($organization)
            ->when($isTrainer, fn ($query) => $query
                ->where('assigned_trainer_user_id', $user->id)
                ->whereIn('home_branch_id', $user->branchAssignments()
                    ->where('organization_id', $organization->id)
                    ->where('status', 'active')
                    ->where(fn ($assignment) => $assignment->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                    ->where(fn ($assignment) => $assignment->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                    ->select('branch_id')))
            ->when(! $isTrainer && $tenantContext->branch() !== null, fn ($query) => $query->where('home_branch_id', $tenantContext->branch()->id));
        $members = (clone $membersQuery)->with([
            'homeBranch',
            'trainer',
            'mealPlans' => fn ($query) => $query->where('status', 'active')->latest(),
        ])->orderBy('name')->get();
        $memberIds = $members->modelKeys();

        return view('fitness.index', [
            'branches' => $organization->branches()->orderBy('name')->get(),
            'members' => $members,
            'workoutDays' => WorkoutAssignment::WEEKDAYS,
            'isTrainer' => $isTrainer,
            'canManageFitness' => $user->hasPermission('fitness.manage', $organization, $tenantContext->branch()),
            'goals' => FitnessGoal::whereBelongsTo($organization)->whereIn('member_id', $memberIds)->with('member')->latest()->limit(50)->get(),
            'workouts' => WorkoutAssignment::whereBelongsTo($organization)->whereIn('member_id', $memberIds)->where('status', 'active')->with('member')->latest()->limit(50)->get(),
            'mealPlans' => MealPlan::whereBelongsTo($organization)->whereIn('member_id', $memberIds)->with('member')->latest()->limit(50)->get(),
            'events' => GymEvent::whereBelongsTo($organization)->with('branch')->latest('starts_at')->limit(50)->get(),
            'notifications' => NotificationDelivery::whereBelongsTo($organization)->latest()->limit(50)->get(),
        ]);
    }

    public function storeGoal(StoreFitnessGoalRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $member = $this->authorizedMember($request->integer('member_id'), $tenantContext);
        FitnessGoal::create([...$request->validated(), 'member_id' => $member->id, 'organization_id' => $member->organization_id, 'trainer_user_id' => $request->user()->id, 'milestones' => [], 'status' => 'active']);

        return back()->with('status', 'Fitness goal created.');
    }

    public function storeWorkout(StoreWorkoutAssignmentRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $member = $this->authorizedMember($request->integer('member_id'), $tenantContext);
        $schedule = collect($request->validated('weekly_schedule'))
            ->only(array_keys(WorkoutAssignment::WEEKDAYS))
            ->mapWithKeys(fn (?string $exercises, string $day): array => [$day => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $exercises ?? '') ?: [])))])
            ->filter(fn (array $exercises): bool => $exercises !== [])
            ->all();
        $exercises = collect($schedule)->flatten()->values()->all();
        $version = ((int) $member->workoutAssignments()->max('version')) + 1;

        DB::transaction(function () use ($member, $request, $schedule, $exercises, $version): void {
            $member->workoutAssignments()->where('status', 'active')->update(['status' => 'replaced']);
            WorkoutAssignment::create([
                ...$request->safe()->except(['weekly_schedule', 'exercises']),
                'organization_id' => $member->organization_id,
                'trainer_user_id' => $member->user_id === $request->user()->id ? null : $request->user()->id,
                'exercises' => $exercises,
                'weekly_schedule' => $schedule,
                'version' => $version,
                'status' => 'active',
            ]);
        });

        return back()->with('status', 'Workout assignment saved as version '.$version.'.');
    }

    public function storeMealPlan(StoreMealPlanRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $validated = $request->validated();
        $members = collect($validated['member_ids'])->map(fn (int $memberId): Member => $this->authorizedMember($memberId, $tenantContext));
        $meals = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $request->string('meals')->toString()) ?: [])));
        $preferences = $request->filled('preferences') ? ['notes' => $request->string('preferences')->toString()] : [];

        DB::transaction(function () use ($members, $validated, $request, $meals, $preferences): void {
            foreach ($members as $member) {
                $version = ((int) $member->mealPlans()->max('version')) + 1;
                $member->mealPlans()->where('status', 'active')->update(['status' => 'replaced']);
                MealPlan::create([
                    'member_id' => $member->id,
                    'organization_id' => $member->organization_id,
                    'author_user_id' => $request->user()->id,
                    'name' => $validated['name'],
                    'starts_on' => $validated['starts_on'],
                    'ends_on' => $validated['ends_on'] ?? null,
                    'meals' => $meals,
                    'preferences' => $preferences,
                    'version' => $version,
                    'status' => 'active',
                ]);
            }
        });

        return back()->with('status', 'Diet plan assigned to '.$members->count().' member'.($members->count() === 1 ? '' : 's').'.');
    }

    public function storeEvent(StoreGymEventRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        $branch = $organization->branches()->findOrFail($request->integer('branch_id'));
        abort_unless($request->user()->hasOrganizationWideRole($organization) || $request->user()->isAssignedToBranch($branch), 403);
        GymEvent::create([...$request->validated(), 'organization_id' => $organization->id, 'organizer_user_id' => $request->user()->id, 'price_paise' => $request->integer('price_paise'), 'status' => 'published']);

        return back()->with('status', 'Event published.');
    }

    private function authorizedMember(int $memberId, TenantContext $tenantContext): Member
    {
        $organization = $tenantContext->requireOrganization();
        $member = Member::whereBelongsTo($organization)->findOrFail($memberId);
        $user = request()->user();
        abort_unless($member->user_id === $user->id || $user->hasOrganizationWideRole($organization) || ($member->assigned_trainer_user_id === $user->id && $user->isAssignedToBranch($member->homeBranch)) || $user->hasPermission('members.update', $organization, $member->homeBranch), 403);

        return $member;
    }
}
