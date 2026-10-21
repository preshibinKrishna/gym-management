<x-layouts.app>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-3xl font-semibold">New branch</h1>
            <p class="mt-1 text-sm text-zinc-600">{{ $organization->name }}</p>
        </div>

        <form method="POST" action="{{ route('branches.store') }}" class="rounded border border-zinc-200 bg-white p-6">
            @csrf
            @include('branches.partials.form')
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('branches.index') }}" class="rounded border border-zinc-300 px-4 py-2 text-sm font-semibold">Cancel</a>
                <button type="submit" class="rounded bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Create branch</button>
            </div>
        </form>
    </div>
</x-layouts.app>
