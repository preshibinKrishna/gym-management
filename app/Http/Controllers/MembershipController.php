<?php

namespace App\Http\Controllers;

use App\Http\Requests\FreezeMembershipRequest;
use App\Http\Requests\PurchaseMembershipRequest;
use App\Http\Requests\RenewMembershipRequest;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\MembershipLifecycleService;
use Illuminate\Http\RedirectResponse;

class MembershipController extends Controller
{
    public function store(PurchaseMembershipRequest $request, MembershipLifecycleService $service): RedirectResponse
    {
        $membership = $service->purchase(
            Member::findOrFail($request->integer('member_id')),
            Plan::findOrFail($request->integer('plan_id')),
            $request->date('starts_on')->toDateString(),
            $request->integer('discount_paise'),
            array_map('intval', $request->input('allowed_branch_ids', [])),
        );

        return redirect()->route('members.show', $membership->member)->with('status', 'Membership purchased.');
    }

    public function freeze(FreezeMembershipRequest $request, MembershipLifecycleService $service): RedirectResponse
    {
        $membership = Membership::findOrFail($request->integer('membership_id'));

        $service->freeze($membership, $request->date('starts_on')->toDateString(), $request->date('ends_on')->toDateString(), $request->string('reason')->toString(), $request->user());

        return redirect()->route('members.show', $membership->member)->with('status', 'Membership frozen and expiry extended.');
    }

    public function renew(RenewMembershipRequest $request, MembershipLifecycleService $service): RedirectResponse
    {
        $membership = Membership::findOrFail($request->integer('membership_id'));
        $renewal = $service->renew($membership, $request->user());

        return redirect()->route('members.show', $renewal->member)->with('status', 'Membership renewed.');
    }
}
