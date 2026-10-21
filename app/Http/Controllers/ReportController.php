<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\FinanceService;
use App\Support\Tenancy\TenantContext;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function finance(TenantContext $tenantContext, FinanceService $financeService): View
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('reports.view', $organization), 403);
        $from = request()->date('from')?->toDateString() ?? now()->startOfMonth()->toDateString();
        $to = request()->date('to')?->toDateString() ?? now()->toDateString();

        return view('reports.finance', ['from' => $from, 'to' => $to, 'summary' => $financeService->collectionsSummary($organization->id, $from, $to)]);
    }

    public function membersCsv(TenantContext $tenantContext): StreamedResponse
    {
        $organization = $tenantContext->requireOrganization();
        abort_unless(request()->user()->hasPermission('exports.create', $organization), 403);
        $members = Member::whereBelongsTo($organization)->with('homeBranch')->orderBy('name')->cursor();

        return response()->streamDownload(function () use ($members): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Member code', 'Name', 'Branch', 'Status', 'Joined on']);
            foreach ($members as $member) {
                fputcsv($handle, [$this->safeCell($member->member_code), $this->safeCell($member->name), $this->safeCell($member->homeBranch?->name), $this->safeCell($member->status), $member->joined_on?->toDateString()]);
            }
            fclose($handle);
        }, 'members.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function safeCell(?string $value): ?string
    {
        return $value !== null && in_array($value[0] ?? '', ['=', '+', '-', '@'], true) ? "'".$value : $value;
    }
}
