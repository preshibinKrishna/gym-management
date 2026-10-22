<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Payment;
use App\Models\WorkoutAssignment;
use App\Support\Tenancy\TenantContext;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        $member = Member::query()
            ->whereBelongsTo($organization)
            ->where('user_id', request()->user()->id)
            ->with([
                'homeBranch',
                'trainer',
                'trainerProfile.homeBranch',
                'homeBranch.manager',
                'memberships.plan',
                'invoices.lines',
                'invoices.installments',
                'attendanceVisits.branch',
                'workoutAssignments' => fn ($query) => $query->where('status', 'active')->latest(),
                'mealPlans' => fn ($query) => $query->where('status', 'active')->latest(),
                'fitnessGoals' => fn ($query) => $query->where('status', 'active')->latest(),
            ])
            ->firstOrFail();

        $invoices = $member->invoices->where('status', '!=', 'draft')->sortByDesc('issued_on')->values();
        $today = now()->toDateString();
        $scheduledInstallments = $invoices->flatMap->installments;
        $upcomingInstallments = $scheduledInstallments->filter(fn ($installment): bool => $installment->status === 'pending' && $installment->due_on->toDateString() >= $today)->sortBy('due_on')->values();
        $overdueInstallments = $scheduledInstallments->filter(fn ($installment): bool => $installment->status === 'pending' && $installment->due_on->toDateString() < $today)->sortBy('due_on')->values();
        $upcomingInvoices = $invoices->filter(fn ($invoice): bool => $invoice->installments->isEmpty() && $invoice->balancePaise() > 0 && $invoice->due_on !== null && $invoice->due_on->toDateString() >= $today)->sortBy('due_on')->values();
        $overdueInvoices = $invoices->filter(fn ($invoice): bool => $invoice->installments->isEmpty() && $invoice->balancePaise() > 0 && $invoice->due_on !== null && $invoice->due_on->toDateString() < $today)->sortBy('due_on')->values();
        $payments = Payment::query()
            ->whereBelongsTo($organization)
            ->where('status', 'received')
            ->where(fn ($query) => $query
                ->where('member_id', $member->id)
                ->orWhereHas('allocations.invoice', fn ($invoicesQuery) => $invoicesQuery->where('member_id', $member->id)))
            ->with(['allocations.invoice', 'refunds'])
            ->orderByDesc('received_on')
            ->get();
        $staff = $member->homeBranch === null
            ? collect()
            : $member->homeBranch->assignments()
                ->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                ->with(['user.roleAssignments' => fn ($query) => $query
                    ->where('organization_id', $organization->id)
                    ->where(fn ($roleQuery) => $roleQuery->whereNull('branch_id')->orWhere('branch_id', $member->homeBranch->id))
                    ->with('role')])
                ->get()
                ->pluck('user')
                ->filter()
                ->push($member->homeBranch->manager)
                ->push($member->trainer)
                ->filter()
                ->unique('id')
                ->map(function ($user) use ($member): array {
                    $roleNames = $user->relationLoaded('roleAssignments')
                        ? $user->roleAssignments->pluck('role.name')->filter()->unique()->implode(', ')
                        : '';

                    return [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'roles' => $roleNames ?: ($user->id === $member->homeBranch->manager_user_id
                            ? 'Branch manager'
                            : ($user->id === $member->assigned_trainer_user_id ? 'Assigned trainer' : 'Gym staff')),
                    ];
                })
                ->values();

        return view('portal.member', [
            'member' => $member,
            'workoutDays' => WorkoutAssignment::WEEKDAYS,
            'upcomingInvoices' => $upcomingInvoices,
            'overdueInvoices' => $overdueInvoices,
            'upcomingInstallments' => $upcomingInstallments,
            'overdueInstallments' => $overdueInstallments,
            'invoices' => $invoices,
            'payments' => $payments,
            'staff' => $staff,
            'outstandingPaise' => $invoices->sum(fn ($invoice): int => $invoice->balancePaise()),
        ]);
    }
}
