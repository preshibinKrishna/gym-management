<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Models\Batch;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        Gate::authorize('viewAny', Batch::class);

        return view('batches.index', [
            'batches' => Batch::query()
                ->whereBelongsTo($tenantContext->requireOrganization())
                ->with(['branch', 'trainer'])
                ->orderBy('starts_at')
                ->paginate(15),
            'activeBatchCount' => $tenantContext->requireOrganization()->batches()->where('status', 'active')->count(),
        ]);
    }

    public function create(TenantContext $tenantContext): View
    {
        Gate::authorize('create', Batch::class);

        return view('batches.create', [
            'branches' => $tenantContext->requireOrganization()->branches()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBatchRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $batch = Batch::create([
            ...$request->validated(),
            'organization_id' => $tenantContext->requireOrganization()->id,
            'ends_next_day' => $request->boolean('ends_next_day'),
            'weekdays' => array_map('intval', $request->input('weekdays', [])),
            'status' => 'active',
        ]);

        return redirect()->route('batches.show', $batch)->with('status', 'Batch created.');
    }

    public function show(Batch $batch, TenantContext $tenantContext): View
    {
        abort_unless($batch->organization_id === $tenantContext->requireOrganization()->id, 404);
        Gate::authorize('view', $batch);

        return view('batches.show', ['batch' => $batch->load(['branch', 'trainer', 'enrollments.member'])]);
    }
}
