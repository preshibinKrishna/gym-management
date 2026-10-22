<?php

namespace Database\Seeders;

use App\Models\AttendanceVisit;
use App\Models\Batch;
use App\Models\BatchEnrollment;
use App\Models\Branch;
use App\Models\FitnessGoal;
use App\Models\GymEvent;
use App\Models\Invoice;
use App\Models\MealPlan;
use App\Models\Member;
use App\Models\Membership;
use App\Models\NotificationDelivery;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\Trainer;
use App\Models\TrainerPayment;
use App\Models\User;
use App\Models\WorkoutAssignment;
use App\Services\FinanceService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoSectionSeeder extends Seeder
{
    /**
     * Seed demo data for the organization workspaces.
     */
    public function run(): void
    {
        Organization::query()
            ->with(['branches', 'members'])
            ->orderBy('code')
            ->get()
            ->each(fn (Organization $organization) => $this->seedOrganization($organization));
    }

    private function seedOrganization(Organization $organization): void
    {
        $branches = $organization->branches;
        $trainerUser = User::query()->where('email', 'trainer.'.mb_strtolower($organization->code).'@example.test')->first();

        if ($branches->isEmpty() || ! $trainerUser) {
            return;
        }

        $branchIds = $branches->pluck('id')->all();
        Membership::query()
            ->whereBelongsTo($organization)
            ->whereIn('member_id', $organization->members()->select('id'))
            ->whereHas('plan', fn ($query) => $query->where('name', 'Monthly Gym Access'))
            ->update(['allowed_branch_ids' => json_encode($branchIds)]);
        $members = $organization->members()->with('memberships')->orderBy('id')->get();

        if ($members->isEmpty()) {
            return;
        }

        $this->seedTrainers($organization, $branches->first()->id);
        $this->seedBatches($organization, $branches->all(), $members->all(), $trainerUser);
        $this->seedAttendance($organization, $branches->all(), $members->all());
        $this->seedFinance($organization, $members->all());
        $this->seedFitness($organization, $branches->all(), $members->all(), $trainerUser);
    }

    private function seedTrainers(Organization $organization, int $branchId): void
    {
        $trainerCount = Trainer::query()->whereBelongsTo($organization)->count();

        for ($trainerNumber = $trainerCount + 1; $trainerNumber <= 50; $trainerNumber++) {
            Trainer::create([
                'organization_id' => $organization->id,
                'home_branch_id' => $branchId,
                'name' => fake()->name(),
                'email' => 'trainer.'.mb_strtolower($organization->code).'.demo.'.$trainerNumber.'@example.test',
                'phone' => fake()->numerify('9#########'),
                'specialization' => fake()->randomElement(['Strength training', 'Conditioning', 'Yoga', 'Mobility', 'Personal training']),
                'joined_on' => now()->subDays(fake()->numberBetween(5, 700))->toDateString(),
                'employment_type' => 'salary',
                'salary_paise' => fake()->numberBetween(1800000, 4500000),
                'status' => 'active',
            ]);
        }

        $trainers = Trainer::query()->whereBelongsTo($organization)->orderBy('id')->limit(50)->get();
        $trainerPaymentCount = TrainerPayment::query()->whereBelongsTo($organization)->count();

        for ($paymentNumber = $trainerPaymentCount + 1; $paymentNumber <= 50; $paymentNumber++) {
            $trainer = $trainers[($paymentNumber - 1) % $trainers->count()];
            TrainerPayment::create([
                'organization_id' => $organization->id,
                'trainer_id' => $trainer->id,
                'amount_paise' => $trainer->salary_paise ?? 2500000,
                'payment_type' => 'salary',
                'method' => 'bank_transfer',
                'paid_on' => now()->startOfMonth()->addDays(min($paymentNumber - 1, now()->day - 1))->toDateString(),
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
                'reference' => 'DEMO-PAYROLL-'.$organization->code.'-'.$paymentNumber,
                'notes' => 'Seeded demo payroll record.',
            ]);
        }
    }

    /**
     * @param  array<int, Branch>  $branches
     * @param  array<int, Member>  $members
     */
    private function seedBatches(Organization $organization, array $branches, array $members, User $trainerUser): void
    {
        $batches = Batch::query()->whereBelongsTo($organization)->orderBy('id')->get();

        for ($batchNumber = $batches->count() + 1; $batchNumber <= 50; $batchNumber++) {
            $branch = $branches[($batchNumber - 1) % count($branches)];
            $startHour = 6 + (($batchNumber - 1) % 12);
            $startsAt = sprintf('%02d:00:00', $startHour);
            $endsAt = sprintf('%02d:00:00', ($startHour + 1) % 24);
            $batch = Batch::factory()->create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'trainer_user_id' => $trainerUser->id,
                'name' => fake()->randomElement(['Strength Foundations', 'HIIT Conditioning', 'Mobility Flow', 'Powerlifting Club', 'Functional Fitness']).' '.$batchNumber,
                'code' => 'DEMO'.str_pad((string) $batchNumber, 3, '0', STR_PAD_LEFT),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'capacity' => 20,
            ]);

            foreach (array_slice($members, 0, min(10, count($members))) as $member) {
                BatchEnrollment::factory()->create([
                    'organization_id' => $organization->id,
                    'batch_id' => $batch->id,
                    'member_id' => $member->id,
                ]);
            }
        }
    }

    /**
     * @param  array<int, Branch>  $branches
     * @param  array<int, Member>  $members
     */
    private function seedAttendance(Organization $organization, array $branches, array $members): void
    {
        foreach ($branches as $branch) {
            $localNow = CarbonImmutable::now($branch->timezone);
            $localDate = $localNow->toDateString();
            $eligibleMembers = collect($members)->filter(fn (Member $member): bool => $member->memberships->contains(fn (Membership $membership): bool => $membership->status === 'active'
                && $membership->starts_on->toDateString() <= $localDate
                && $membership->ends_on->toDateString() >= $localDate
                && in_array($branch->id, $membership->allowed_branch_ids ?? [], true)
            ))->values();
            $visitCount = AttendanceVisit::query()
                ->whereBelongsTo($organization)
                ->whereBelongsTo($branch)
                ->whereDate('checked_in_at', $localDate)
                ->count();

            if ($eligibleMembers->isEmpty()) {
                continue;
            }

            for ($visitNumber = $visitCount + 1; $visitNumber <= 50; $visitNumber++) {
                $member = $eligibleMembers[($visitNumber - 1) % $eligibleMembers->count()];
                $membership = $member->memberships
                    ->filter(fn (Membership $membership): bool => $membership->status === 'active'
                        && $membership->starts_on->toDateString() <= $localDate
                        && $membership->ends_on->toDateString() >= $localDate
                        && in_array($branch->id, $membership->allowed_branch_ids ?? [], true))
                    ->sortByDesc('ends_on')
                    ->first();
                $batch = Batch::query()
                    ->whereBelongsTo($organization)
                    ->whereBelongsTo($branch)
                    ->where('status', 'active')
                    ->orderBy('id')
                    ->first();

                AttendanceVisit::factory()->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $branch->id,
                    'member_id' => $member->id,
                    'membership_id' => $membership?->id,
                    'batch_id' => $batch?->id,
                    'checked_in_at' => $localNow->subMinutes(min($visitNumber - 1, ($localNow->hour * 60) + $localNow->minute)),
                    'idempotency_key' => 'DEMO-'.$organization->code.'-'.$branch->id.'-'.$localDate.'-'.$visitNumber,
                ]);
            }
        }
    }

    /**
     * @param  array<int, Member>  $members
     */
    private function seedFinance(Organization $organization, array $members): void
    {
        $financeService = app(FinanceService::class);
        $paymentCount = Payment::query()->whereBelongsTo($organization)->count();

        for ($paymentNumber = $paymentCount + 1; $paymentNumber <= 50; $paymentNumber++) {
            $member = $members[($paymentNumber - 1) % count($members)];
            $invoice = Invoice::query()
                ->whereBelongsTo($organization)
                ->where('member_id', $member->id)
                ->whereColumn('paid_paise', '<', 'total_paise')
                ->orderBy('id')
                ->first();

            if (! $invoice) {
                $invoice = $financeService->issueInvoice([
                    'organization_id' => $organization->id,
                    'member_id' => $member->id,
                    'number' => 'DEMO-COLLECT-'.$organization->code.'-'.$paymentNumber,
                    'due_on' => now()->endOfMonth()->toDateString(),
                ], [[
                    'description' => 'Monthly Gym Access',
                    'quantity' => 1,
                    'unit_price_paise' => 250000,
                ]]);
            }

            $amount = $invoice->balancePaise();
            $financeService->receivePayment([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'amount_paise' => $amount,
                'method' => ['cash', 'upi', 'bank_transfer', 'card'][($paymentNumber - 1) % 4],
                'idempotency_key' => 'DEMO-COLLECTION-'.$organization->code.'-'.$paymentNumber,
                'received_on' => now()->startOfMonth()->addDays(min($paymentNumber - 1, now()->day - 1))->toDateString(),
                'notes' => 'Seeded demo collection.',
            ], [[
                'invoice_id' => $invoice->id,
                'amount_paise' => $amount,
            ]]);
        }
    }

    /**
     * @param  array<int, Branch>  $branches
     * @param  array<int, Member>  $members
     */
    private function seedFitness(Organization $organization, array $branches, array $members, User $trainerUser): void
    {
        $goalCount = FitnessGoal::query()->whereBelongsTo($organization)->count();

        for ($recordNumber = $goalCount + 1; $recordNumber <= 50; $recordNumber++) {
            $member = $members[($recordNumber - 1) % count($members)];
            FitnessGoal::create([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'trainer_user_id' => $trainerUser->id,
                'category' => ['strength', 'endurance', 'mobility', 'weight_management'][($recordNumber - 1) % 4],
                'title' => ['Build consistent strength', 'Improve cardiovascular endurance', 'Increase mobility', 'Reach a healthy weight'][($recordNumber - 1) % 4],
                'notes' => 'Demo goal reviewed during the member coaching check-in.',
                'starts_on' => now()->startOfMonth()->toDateString(),
                'target_on' => now()->addMonthsNoOverflow(3)->toDateString(),
                'review_on' => now()->addMonthNoOverflow()->toDateString(),
                'status' => 'active',
                'milestones' => [],
            ]);
        }

        $workoutCount = WorkoutAssignment::query()->whereBelongsTo($organization)->count();

        for ($recordNumber = $workoutCount + 1; $recordNumber <= 50; $recordNumber++) {
            $member = $members[($recordNumber - 1) % count($members)];
            WorkoutAssignment::create([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'trainer_user_id' => $trainerUser->id,
                'name' => ['Full Body Foundations', 'Strength Progression', 'Conditioning and Core', 'Mobility Reset'][($recordNumber - 1) % 4],
                'starts_on' => now()->startOfMonth()->toDateString(),
                'ends_on' => now()->addWeeks(4)->toDateString(),
                'exercises' => ['Squat', 'Row', 'Press', 'Core stability'],
                'instructions' => 'Complete the session with a trainer and record working weights.',
                'status' => 'active',
                'version' => ((int) $member->workoutAssignments()->max('version')) + 1,
            ]);
        }

        $mealPlanCount = MealPlan::query()->whereBelongsTo($organization)->count();

        for ($recordNumber = $mealPlanCount + 1; $recordNumber <= 50; $recordNumber++) {
            $member = $members[($recordNumber - 1) % count($members)];
            MealPlan::create([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'author_user_id' => $trainerUser->id,
                'name' => ['Balanced Training Week', 'Recovery and Hydration', 'Lean Muscle Support'][($recordNumber - 1) % 3],
                'starts_on' => now()->startOfMonth()->toDateString(),
                'ends_on' => now()->addWeeks(4)->toDateString(),
                'meals' => ['Breakfast: oats, fruit, and eggs', 'Lunch: rice, vegetables, and lean protein', 'Snack: yogurt and nuts', 'Dinner: lentils and vegetables'],
                'preferences' => ['notes' => 'Sample plan; confirm dietary needs with the member.'],
                'status' => 'active',
                'version' => ((int) $member->mealPlans()->max('version')) + 1,
            ]);
        }

        $eventCount = GymEvent::query()->whereBelongsTo($organization)->count();

        for ($recordNumber = $eventCount + 1; $recordNumber <= 50; $recordNumber++) {
            $startsAt = now()->addDays($recordNumber % 30)->setTime(10 + ($recordNumber % 8), 0);
            GymEvent::create([
                'organization_id' => $organization->id,
                'branch_id' => $branches[($recordNumber - 1) % count($branches)]->id,
                'organizer_user_id' => $trainerUser->id,
                'name' => ['Community Strength Session', 'Weekend Mobility Workshop', 'Fitness Assessment Day', 'Group Conditioning Class'][($recordNumber - 1) % 4].' '.$recordNumber,
                'description' => 'Demo event for members to train and connect with the coaching team.',
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addHour(),
                'capacity' => 30,
                'price_paise' => 0,
                'status' => 'published',
            ]);
        }

        $notificationCount = NotificationDelivery::query()->whereBelongsTo($organization)->count();

        for ($recordNumber = $notificationCount + 1; $recordNumber <= 50; $recordNumber++) {
            NotificationDelivery::create([
                'organization_id' => $organization->id,
                'member_id' => $members[($recordNumber - 1) % count($members)]->id,
                'channel' => ['email', 'sms', 'whatsapp'][($recordNumber - 1) % 3],
                'template' => 'member_progress_update',
                'status' => 'sent',
                'payload' => ['message' => 'Your coach has a new training update for you.'],
                'sent_at' => now()->subDays($recordNumber % 14),
                'idempotency_key' => 'DEMO-NOTIFICATION-'.$organization->code.'-'.$recordNumber,
            ]);
        }
    }
}
