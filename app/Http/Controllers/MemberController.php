<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Models\GuardianRelationship;
use App\Models\Member;
use App\Models\OrganizationAffiliation;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        Gate::authorize('viewAny', Member::class);

        $month = request()->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? null;
        $period = $month ? CarbonImmutable::createFromFormat('!Y-m', $month) : now()->startOfMonth();
        $periodStart = $period->startOfMonth()->toDateString();
        $periodEnd = $period->endOfMonth()->toDateString();

        $members = Member::query()
            ->whereBelongsTo($tenantContext->requireOrganization())
            ->with([
                'homeBranch',
                'trainer',
                'memberships' => fn ($query) => $query->where('status', 'active')->whereDate('starts_on', '<=', $periodEnd)->whereDate('ends_on', '>=', $periodStart)->with('plan'),
                'invoices' => fn ($query) => $query->whereBetween('issued_on', [$periodStart, $periodEnd])->orderByDesc('issued_on'),
            ])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'paid' => $members->getCollection()->filter(fn (Member $member): bool => $member->monthlyPaymentStatus() === 'paid')->count(),
            'unpaid' => $members->getCollection()->filter(fn (Member $member): bool => in_array($member->monthlyPaymentStatus(), ['unpaid', 'partial'], true))->count(),
            'not_billed' => $members->getCollection()->filter(fn (Member $member): bool => $member->monthlyPaymentStatus() === 'not_billed')->count(),
        ];

        return view('members.index', compact('members', 'period', 'summary'));
    }

    public function create(TenantContext $tenantContext): View
    {
        Gate::authorize('create', Member::class);

        $organization = $tenantContext->requireOrganization();

        return view('members.create', [
            'branches' => $organization->branches()->orderBy('name')->get(),
            'trainers' => User::query()
                ->whereHas('affiliations', fn ($query) => $query->where('organization_id', $organization->id)->where('status', 'active'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreMemberRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        $validated = $request->safe()->except(['avatar', 'guardian_name', 'guardian_email', 'guardian_relationship']);

        $member = Member::create([
            ...$validated,
            'organization_id' => $organization->id,
            'status' => 'active',
            'goals' => $request->input('goals', []),
            'consents' => ['waiver_v1' => now()->toDateString()],
        ]);

        if ($request->hasFile('avatar')) {
            $member->update(['avatar_path' => $request->file('avatar')->store('member-avatars', 'public')]);
        }

        if ($request->filled('guardian_email') && $request->filled('guardian_name')) {
            $guardian = User::firstOrCreate(
                ['email' => $request->string('guardian_email')->lower()->toString()],
                [
                    'name' => $request->string('guardian_name')->toString(),
                    'password' => Hash::make('DevOnly!2026'),
                    'status' => 'active',
                ],
            );

            OrganizationAffiliation::firstOrCreate(
                ['organization_id' => $organization->id, 'user_id' => $guardian->id],
                ['status' => 'active', 'verified_at' => now(), 'invited_by_user_id' => $request->user()->id],
            );

            GuardianRelationship::create([
                'organization_id' => $organization->id,
                'member_id' => $member->id,
                'guardian_user_id' => $guardian->id,
                'relationship' => $request->input('guardian_relationship', 'guardian'),
                'review_required_at' => $member->date_of_birth->addYears(18),
            ]);
        }

        return redirect()->route('members.show', $member)->with('status', 'Member registered.');
    }

    public function updatePhoto(Request $request, Member $member, TenantContext $tenantContext): RedirectResponse
    {
        abort_unless($member->organization_id === $tenantContext->requireOrganization()->id, 404);
        abort_unless($request->user()->hasPermission('members.update', $tenantContext->requireOrganization(), $member->homeBranch), 403);
        $validated = $request->validate(['avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);

        if ($member->avatar_path) {
            Storage::disk('public')->delete($member->avatar_path);
        }
        $member->update(['avatar_path' => $validated['avatar']->store('member-avatars', 'public')]);

        return back()->with('status', 'Member profile photo updated.');
    }

    public function show(Member $member, TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();

        abort_unless($member->organization_id === $organization->id, 404);
        Gate::authorize('view', $member);

        $member->load(['homeBranch', 'trainer', 'memberships.plan', 'guardianRelationships.guardian', 'attendanceVisits.branch']);

        return view('members.show', ['member' => $member]);
    }
}
