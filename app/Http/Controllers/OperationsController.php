<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOperationalRecordRequest;
use App\Models\Asset;
use App\Models\CctvCamera;
use App\Models\Expense;
use App\Models\MaintenanceWorkOrder;
use App\Models\OperationalTask;
use App\Models\Vendor;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('operations.view', $organization, $tenantContext->branch()), 403);

        $assets = Asset::where('organization_id', $organization->id)->with('branch')->latest()->paginate(10, ['*'], 'asset_page')->withQueryString();
        $assetCounts = Asset::where('organization_id', $organization->id)->selectRaw('category, COUNT(*) as total')->groupBy('category')->pluck('total', 'category');

        return view('operations.index', ['branches' => $organization->branches()->orderBy('name')->get(), 'vendors' => Vendor::where('organization_id', $organization->id)->latest()->limit(10)->get(), 'assets' => $assets, 'assetCounts' => $assetCounts, 'workOrders' => MaintenanceWorkOrder::where('organization_id', $organization->id)->with('asset')->latest()->limit(10)->get(), 'tasks' => OperationalTask::where('organization_id', $organization->id)->latest()->limit(10)->get(), 'expenses' => Expense::where('organization_id', $organization->id)->latest()->limit(10)->get(), 'cameras' => CctvCamera::where('organization_id', $organization->id)->with('branch')->latest()->limit(10)->get()]);
    }

    public function store(StoreOperationalRecordRequest $request, TenantContext $tenantContext): RedirectResponse
    {
        $organization = $tenantContext->requireOrganization();
        $data = $request->validated();
        $type = $data['record_type'];
        unset($data['record_type']);
        if ($type === 'vendor') {
            Vendor::create([...$data, 'organization_id' => $organization->id, 'status' => 'active']);
        }
        if ($type === 'asset') {
            $branch = $organization->branches()->findOrFail($request->integer('branch_id'));
            Asset::create([...$data, 'organization_id' => $organization->id, 'branch_id' => $branch->id, 'status' => 'active']);
        }
        if ($type === 'maintenance') {
            MaintenanceWorkOrder::create([...$data, 'organization_id' => $organization->id, 'status' => 'reported']);
        }
        if ($type === 'task') {
            OperationalTask::create([...$data, 'organization_id' => $organization->id, 'status' => 'open']);
        }
        if ($type === 'expense') {
            Expense::create([...$data, 'organization_id' => $organization->id, 'status' => 'draft', 'bill_on' => $data['bill_on'] ?? now()->toDateString()]);
        }
        if ($type === 'camera') {
            $branch = $organization->branches()->findOrFail($request->integer('branch_id'));
            CctvCamera::create([...$data, 'organization_id' => $organization->id, 'branch_id' => $branch->id, 'status' => 'unconfigured']);
        }

        return back()->with('status', ucfirst($type).' record created.');
    }
}
