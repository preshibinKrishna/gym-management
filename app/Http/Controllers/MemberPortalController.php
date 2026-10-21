<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Support\Tenancy\TenantContext;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function __invoke(TenantContext $tenantContext): View
    {
        $member = Member::query()
            ->whereBelongsTo($tenantContext->requireOrganization())
            ->where('user_id', request()->user()->id)
            ->with(['memberships.plan', 'attendanceVisits.branch'])
            ->firstOrFail();

        return view('portal.member', ['member' => $member]);
    }
}
