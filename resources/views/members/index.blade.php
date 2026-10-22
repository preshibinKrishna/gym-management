<x-layouts.app>
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Member directory</p>
            <h1 class="mt-2 text-3xl font-semibold">Members</h1>
            <p class="mt-2 text-sm text-zinc-600">Track packages, monthly billing, and collection status in one view.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <form method="GET" action="{{ route('members.index') }}" class="surface flex items-end gap-3 p-2.5">
                <label class="px-1 text-xs font-semibold uppercase text-zinc-500">Billing month<input type="month" name="month" value="{{ $period->format('Y-m') }}" class="field min-w-36"></label>
                <button type="submit" class="btn-secondary">View month</button>
            </form>
            <a href="{{ route('members.create') }}" class="btn-primary">+ Register member</a>
        </div>
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="metric-card"><p class="eyebrow">Collected</p><p class="mt-3 text-sm text-zinc-500">Paid this month</p><p class="mt-1 text-3xl font-semibold text-emerald-800">{{ $summary['paid'] }}</p><p class="mt-1 text-xs text-zinc-500">Members fully settled</p></div>
        <div class="metric-card"><p class="eyebrow">Follow up</p><p class="mt-3 text-sm text-zinc-500">Payment pending</p><p class="mt-1 text-3xl font-semibold text-amber-700">{{ $summary['unpaid'] }}</p><p class="mt-1 text-xs text-zinc-500">Unpaid or partially paid</p></div>
        <div class="metric-card"><p class="eyebrow">Attention</p><p class="mt-3 text-sm text-zinc-500">Not billed</p><p class="mt-1 text-3xl font-semibold text-zinc-700">{{ $summary['not_billed'] }}</p><p class="mt-1 text-xs text-zinc-500">No invoice for this month</p></div>
    </section>

    <section class="surface mt-8 overflow-hidden">
        <div class="flex flex-col justify-between gap-3 border-b border-line px-5 py-5 sm:flex-row sm:items-center sm:px-6">
            <div><p class="eyebrow">{{ $period->format('F Y') }}</p><h2 class="mt-1 text-lg font-semibold">Membership billing overview</h2></div>
            <p class="text-xs text-zinc-500">{{ $members->total() }} total members</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[1160px] w-full text-left text-sm">
                <thead><tr><th class="px-5 py-3">Member</th><th class="px-5 py-3">Package</th><th class="px-5 py-3">Joined</th><th class="px-5 py-3">Member status</th><th class="px-5 py-3">Monthly fee</th><th class="px-5 py-3">Paid</th><th class="px-5 py-3">Balance</th><th class="px-5 py-3">Payment status</th><th class="px-5 py-3"></th></tr></thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($members as $member)
                        @php
                            $membership = $member->memberships->sortByDesc('ends_on')->first();
                            $status = $member->monthlyPaymentStatus();
                            $statusLabel = ['paid' => 'Paid', 'partial' => 'Partially paid', 'unpaid' => 'Not paid', 'not_billed' => 'Not billed'][$status];
                            $statusClasses = ['paid' => 'bg-emerald-100 text-emerald-800', 'partial' => 'bg-amber-100 text-amber-800', 'unpaid' => 'bg-rose-100 text-rose-800', 'not_billed' => 'bg-zinc-100 text-zinc-700'][$status];
                        @endphp
                        <tr>
                            <td class="px-5 py-4"><div class="flex items-center gap-3"><span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-full bg-ink text-xs font-bold text-mint ring-2 ring-emerald-50">@if($member->avatar_path)<img src="{{ asset('storage/'.$member->avatar_path) }}" alt="{{ $member->name }}" class="size-full object-cover">@else{{ str($member->name)->substr(0, 1)->upper() }}@endif</span><div><a href="{{ route('members.show', $member) }}" class="font-semibold text-ink hover:text-emerald-700">{{ $member->name }}</a><p class="mt-0.5 font-mono text-[11px] text-zinc-500">{{ $member->member_code }}</p></div></div></td>
                            <td class="px-5 py-4"><p class="font-medium">{{ $membership?->plan?->name ?? 'No active package' }}</p><p class="mt-1 text-xs text-zinc-500">{{ $member->homeBranch?->name ?? 'No branch' }}</p></td>
                            <td class="px-5 py-4 text-zinc-600">{{ $member->joined_on?->format('d M Y') ?? 'Not recorded' }}</td>
                            <td class="px-5 py-4"><span class="status-pill {{ $member->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-600' }}">{{ ucfirst($member->status) }}</span></td>
                            <td class="px-5 py-4 font-medium">{{ $member->monthlyBilledPaise() ? '₹'.number_format($member->monthlyBilledPaise() / 100, 2) : '—' }}</td>
                            <td class="px-5 py-4 text-emerald-800">{{ $member->monthlyPaidPaise() ? '₹'.number_format($member->monthlyPaidPaise() / 100, 2) : '—' }}</td>
                            <td class="px-5 py-4 {{ $member->monthlyBilledPaise() - $member->monthlyPaidPaise() > 0 ? 'font-semibold text-rose-700' : 'text-zinc-500' }}">{{ $member->monthlyBilledPaise() - $member->monthlyPaidPaise() > 0 ? '₹'.number_format(($member->monthlyBilledPaise() - $member->monthlyPaidPaise()) / 100, 2) : '—' }}</td>
                            <td class="px-5 py-4"><span class="status-pill {{ $statusClasses }}">{{ $statusLabel }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('members.show', $member) }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950">View profile →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-14 text-center"><p class="font-semibold text-ink">No members found</p><p class="mt-1 text-sm text-zinc-500">Register a member or choose another billing month.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-line px-5 py-4">{{ $members->links() }}</div>
    </section>
</x-layouts.app>
