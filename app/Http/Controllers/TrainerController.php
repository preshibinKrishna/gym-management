<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainerPaymentRequest;
use App\Http\Requests\StoreTrainerRequest;
use App\Models\Trainer;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainerController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('trainers.view', $organization), 403);

        $status = request()->validate(['status' => ['nullable', 'in:active,inactive']])['status'] ?? null;
        $trainers = Trainer::query()
            ->whereBelongsTo($organization)
            ->with('homeBranch')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('trainers.index', [
            'trainers' => $trainers,
            'status' => $status,
            'activeCount' => $organization->trainers()->where('status', 'active')->count(),
            'inactiveCount' => $organization->trainers()->where('status', 'inactive')->count(),
            'monthlyPayrollPaise' => (int) $organization->trainers()->where('status', 'active')->where('employment_type', 'salary')->sum('salary_paise'),
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('trainers.manage', $organization), 403);

        return view('trainers.create', [
            'branches' => $organization->branches()->orderBy('name')->get(),
            'users' => $organization->affiliations()->where('status', 'active')->with('user')->get()->pluck('user')->filter()->unique('id')->sortBy('name'),
        ]);
    }

    public function store(StoreTrainerRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        $validated = $request->safe()->except('avatar');

        $trainer = Trainer::create([...$validated, 'organization_id' => $organization->id, 'status' => 'active']);

        if ($request->hasFile('avatar')) {
            $trainer->update(['avatar_path' => $request->file('avatar')->store('trainer-avatars', 'public')]);
        }

        return redirect()->route('trainers.show', $trainer)->with('status', 'Trainer profile created.');
    }

    public function show(Trainer $trainer, TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless($trainer->organization_id === $organization->id, 404);
        abort_unless(request()->user()->hasPermission('trainers.view', $organization, $trainer->homeBranch), 403);

        $trainer->load(['homeBranch', 'user']);
        $payments = $trainer->payments()->latest('paid_on')->paginate(12)->withQueryString();

        return view('trainers.show', [
            'trainer' => $trainer,
            'payments' => $payments,
            'totalPaidPaise' => (int) $trainer->payments()->sum('amount_paise'),
        ]);
    }

    public function updateStatus(Request $request, Trainer $trainer, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless($trainer->organization_id === $organization->id, 404);
        abort_unless($request->user()->hasPermission('trainers.manage', $organization, $trainer->homeBranch), 403);

        $trainer->update($request->validate(['status' => ['required', 'in:active,inactive']]));

        return back()->with('status', 'Trainer status updated.');
    }

    public function storePayment(StoreTrainerPaymentRequest $request, Trainer $trainer, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless($trainer->organization_id === $organization->id, 404);
        abort_unless($request->user()->hasPermission('trainers.manage', $organization, $trainer->homeBranch), 403);

        $trainer->payments()->create(['organization_id' => $organization->id, ...$request->validated()]);

        return back()->with('status', 'Trainer payment recorded.');
    }
}
