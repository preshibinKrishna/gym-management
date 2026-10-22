<?php

namespace App\Http\Controllers;

use App\Models\GymEnquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformGymEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->is_platform_super_admin, 403);

        $statusFilter = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(GymEnquiry::STATUS_OPTIONS))],
        ])['status'] ?? null;

        $statusCounts = GymEnquiry::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $enquiries = GymEnquiry::query()
            ->when($statusFilter, fn ($query) => $query->where('status', $statusFilter))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('platform.gym-enquiries.index', [
            'enquiries' => $enquiries,
            'statusCounts' => $statusCounts,
            'statusFilter' => $statusFilter,
            'statusOptions' => GymEnquiry::STATUS_OPTIONS,
        ]);
    }
}
