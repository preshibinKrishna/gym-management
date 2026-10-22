<x-layouts.app>
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="eyebrow">Platform sales pipeline</p>
            <h1 class="mt-2 text-3xl font-semibold">Gym enquiries</h1>
            <p class="mt-2 text-sm text-zinc-600">Track gym owners exploring the platform and their path to signup.</p>
        </div>
        <p class="rounded-xl border border-line bg-white px-4 py-3 text-sm font-semibold text-zinc-600">{{ $enquiries->total() }} prospective gyms</p>
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach($statusOptions as $key => $label)
            <a href="{{ route('platform.gym-enquiries.index', ['status' => $key]) }}" class="metric-card {{ $statusFilter === $key ? 'ring-2 ring-emerald-500' : '' }}">
                <p class="eyebrow">Gym pipeline</p>
                <p class="mt-3 text-sm text-zinc-500">{{ $label }}</p>
                <p class="mt-1 text-3xl font-semibold">{{ $statusCounts[$key] ?? 0 }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $statusFilter === $key ? 'Currently filtered' : 'View enquiries' }}</p>
            </a>
        @endforeach
    </section>

    <section class="surface mt-8 overflow-hidden">
        <div class="flex flex-col justify-between gap-3 border-b border-line px-5 py-5 sm:flex-row sm:items-center sm:px-6">
            <div>
                <p class="eyebrow">{{ $statusFilter ? $statusOptions[$statusFilter] : 'All statuses' }}</p>
                <h2 class="mt-1 text-lg font-semibold">Prospective gym owners</h2>
            </div>
            <a href="{{ route('platform.gym-enquiries.index') }}" class="text-xs font-semibold text-emerald-800 hover:text-emerald-950">Clear filter</a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-left text-sm">
                <thead>
                    <tr>
                        <th class="px-5 py-3">Gym and owner</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">City</th>
                        <th class="px-5 py-3">Source</th>
                        <th class="px-5 py-3">Follow-up</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($enquiries as $enquiry)
                        @php($statusClasses = ['new' => 'bg-zinc-100 text-zinc-700', 'contacted' => 'bg-blue-100 text-blue-800', 'follow_up' => 'bg-amber-100 text-amber-800', 'demo_scheduled' => 'bg-violet-100 text-violet-800', 'signed_up' => 'bg-emerald-100 text-emerald-800', 'closed' => 'bg-rose-100 text-rose-800'])
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-ink">{{ $enquiry->gym_name }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $enquiry->owner_name }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p>{{ $enquiry->email }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $enquiry->phone ?: 'No phone' }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $enquiry->city ?: '—' }}</td>
                            <td class="px-5 py-4">{{ $enquiry->source ?: '—' }}</td>
                            <td class="px-5 py-4 text-xs {{ $enquiry->follow_up_on?->isPast() ? 'font-semibold text-rose-700' : 'text-zinc-500' }}">{{ $enquiry->follow_up_on?->format('d M Y') ?? 'Not set' }}</td>
                            <td class="px-5 py-4"><span class="status-pill {{ $statusClasses[$enquiry->status] ?? 'bg-zinc-100 text-zinc-700' }}">{{ $statusOptions[$enquiry->status] ?? ucfirst($enquiry->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-zinc-500">No gym enquiries match this stage.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line px-5 py-4">{{ $enquiries->links() }}</div>
    </section>
</x-layouts.app>
