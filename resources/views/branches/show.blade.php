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

        @can('assign', $branch)
            <section class="rounded border border-zinc-200 bg-white p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Staff access</p>
                    <h2 class="mt-1 text-lg font-semibold">Choose who can access {{ $branch->name }}</h2>
                    <p class="mt-1 text-sm text-zinc-600">Assigned trainers, gym admins, and other staff can switch to this gym and see its member list.</p>
                </div>
                <form method="POST" action="{{ route('branches.assignments.update', $branch) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($staff as $user)
                            <label class="flex items-center gap-3 rounded border border-zinc-200 p-3 text-sm">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" @checked(in_array($user->id, old('user_ids', $assignedStaffIds))) class="rounded border-zinc-300 text-emerald-700">
                                <span><span class="block font-medium">{{ $user->name }}</span><span class="text-xs text-zinc-500">{{ $user->email }}</span></span>
                            </label>
                        @endforeach
                    </div>
                    @error('user_ids')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                    @error('user_ids.*')<p class="text-sm text-red-700">{{ $message }}</p>@enderror
                    <button class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Save staff access</button>
                </form>
            </section>
        @endcan
    </div>
</x-layouts.app>
