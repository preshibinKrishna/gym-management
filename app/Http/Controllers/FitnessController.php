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
use Illuminate\View\View;

class FitnessController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('fitness.view', $organization, $tenantContext->branch()), 403);

        return view('fitness.index', ['branches' => $organization->branches()->orderBy('name')->get(), 'members' => Member::whereBelongsTo($organization)->orderBy('name')->get(), 'goals' => FitnessGoal::whereBelongsTo($organization)->with('member')->latest()->limit(10)->get(), 'workouts' => WorkoutAssignment::whereBelongsTo($organization)->with('member')->latest()->limit(10)->get(), 'mealPlans' => MealPlan::whereBelongsTo($organization)->with('member')->latest()->limit(10)->get(), 'events' => GymEvent::whereBelongsTo($organization)->with('branch')->latest('starts_at')->limit(10)->get(), 'notifications' => NotificationDelivery::whereBelongsTo($organization)->latest()->limit(10)->get()]);
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
        $version = ((int) $member->workoutAssignments()->max('version')) + 1;
        WorkoutAssignment::create([...$request->safe()->except('exercises'), 'organization_id' => $member->organization_id, 'trainer_user_id' => $request->user()->id, 'exercises' => array_values(array_filter(array_map('trim', explode(',', $request->string('exercises')->toString())))), 'version' => $version, 'status' => 'active']);

        return back()->with('status', 'Workout assignment saved as version '.$version.'.');
    }

    public function storeMealPlan(StoreMealPlanRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $member = $this->authorizedMember($request->integer('member_id'), $tenantContext);
        $version = ((int) $member->mealPlans()->max('version')) + 1;
        MealPlan::create([...$request->safe()->except(['meals', 'preferences']), 'organization_id' => $member->organization_id, 'author_user_id' => $request->user()->id, 'meals' => array_values(array_filter(array_map('trim', explode(',', $request->string('meals')->toString())))), 'preferences' => $request->filled('preferences') ? ['notes' => $request->string('preferences')->toString()] : [], 'version' => $version, 'status' => 'active']);

        return back()->with('status', 'Meal plan saved as version '.$version.'.');
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
        abort_unless($user->hasOrganizationWideRole($organization) || $member->assigned_trainer_user_id === $user->id || $user->hasPermission('members.update', $organization, $member->homeBranch), 403);

        return $member;
    }
}
