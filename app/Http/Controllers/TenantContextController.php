<?php

namespace App\Http\Controllers;

use App\Http\Requests\SwitchTenantContextRequest;
use Illuminate\Http\RedirectResponse;

class TenantContextController extends Controller
{
    public function update(SwitchTenantContextRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->session()->put('tenant.organization_id', (int) $validated['organization_id']);
        $request->session()->put('tenant.branch_id', isset($validated['branch_id']) ? (int) $validated['branch_id'] : null);

        return back()->with('status', 'Workspace switched.');
    }
}
