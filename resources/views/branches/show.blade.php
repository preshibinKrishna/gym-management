<x-layouts.app>
    <div class="space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm font-medium text-emerald-700">{{ $organization->name }}</p>
                <h1 class="mt-1 text-3xl font-semibold">{{ $branch->name }}</h1>
                <p class="mt-1 text-sm text-zinc-600">{{ $branch->code }} · {{ $branch->timezone }}</p>
            </div>
            <a href="{{ route('branches.index') }}" class="rounded border border-zinc-300 px-4 py-2 text-sm font-semibold">All branches</a>
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Status</p>
                <p class="mt-2 text-xl font-semibold">{{ ucfirst($branch->status) }}</p>
            </div>
            <div class="rounded border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Manager</p>
                <p class="mt-2 text-xl font-semibold">{{ $branch->manager?->name ?? 'Unassigned' }}</p>
            </div>
            <div class="rounded border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-500">Capacity</p>
                <p class="mt-2 text-xl font-semibold">{{ $branch->capacity ?? 'Not set' }}</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded border border-zinc-200 bg-white">
                <div class="border-b border-zinc-200 px-5 py-4">
                    <h2 class="font-semibold">Operating intervals</h2>
                </div>
                <div class="divide-y divide-zinc-100">
                    @forelse ($branch->operatingIntervals as $interval)
                        <div class="px-5 py-3 text-sm">
                            Day {{ $interval->weekday }} · {{ $interval->opens_at }} to {{ $interval->closes_at }} {{ $interval->closes_next_day ? '(next day)' : '' }} · {{ ucfirst($interval->eligibility) }}
                        </div>
                    @empty
                        <div class="px-5 py-6 text-sm text-zinc-600">No hours configured.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded border border-zinc-200 bg-white">
                <div class="border-b border-zinc-200 px-5 py-4">
                    <h2 class="font-semibold">Closures and overrides</h2>
                </div>
                <div class="divide-y divide-zinc-100">
                    @forelse ($branch->closures as $closure)
                        <div class="px-5 py-3 text-sm">
                            <p class="font-medium">{{ $closure->title }}</p>
                            <p class="text-zinc-600">{{ $closure->starts_on->format('d M Y') }} to {{ $closure->ends_on->format('d M Y') }}</p>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-sm text-zinc-600">No closures configured.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
