<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInMemberRequest;
use App\Models\AttendanceVisit;
use App\Models\Batch;
use App\Models\Member;
use App\Services\AttendanceCheckInService;
use App\Support\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(TenantContext $tenantContext): View
    {
        $organization = $tenantContext->requireOrganization();
        $branch = $tenantContext->requireBranch();

        $date = request()->validate(['date' => ['nullable', 'date']])['date'] ?? now($branch->timezone)->toDateString();
        $batchOptions = Batch::query()->whereBelongsTo($organization)->whereBelongsTo($branch)->where('status', 'active')->orderBy('starts_at')->get();

        abort_unless(request()->user()?->hasPermission('attendance.checkin', $organization, $branch), 403);

        return view('attendance.index', [
            'branch' => $branch,
            'members' => Member::query()->whereBelongsTo($organization)->whereBelongsTo($branch, 'homeBranch')->orderBy('name')->limit(100)->get(),
            'visits' => AttendanceVisit::query()
                ->whereBelongsTo($organization)
                ->whereBelongsTo($branch)
                ->with(['member', 'membership'])
                ->whereDate('checked_in_at', $date)
                ->latest('checked_in_at')
                ->paginate(20)
                ->withQueryString(),
            'batchAttendance' => Batch::query()
                ->whereBelongsTo($organization)
                ->whereBelongsTo($branch)
                ->where('status', 'active')
                ->with(['enrollments' => fn ($query) => $query->where('status', 'active')->with('member'), 'attendanceVisits' => fn ($query) => $query->whereDate('checked_in_at', $date)->with('member')->latest('checked_in_at')])
                ->orderBy('starts_at')
                ->paginate(10, ['*'], 'batch_page')
                ->withQueryString(),
            'batchOptions' => $batchOptions,
            'attendanceDate' => CarbonImmutable::parse($date),
        ]);
    }

    public function store(CheckInMemberRequest $request, TenantContext $tenantContext, AttendanceCheckInService $service): RedirectResponse
    {
        $visit = $service->checkIn(
            Member::findOrFail($request->integer('member_id')),
            $tenantContext->requireBranch(),
            $request->user(),
            $request->string('idempotency_key')->toString(),
            $request->input('override_reason'),
            $request->integer('batch_id') ?: null,
        );

        return redirect()->route('attendance.index')->with('status', 'Checked in '.$visit->member->name.'.');
    }
}
