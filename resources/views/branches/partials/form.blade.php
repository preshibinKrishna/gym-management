<div class="grid gap-4 sm:grid-cols-2">
    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Name</span>
        <input name="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">
        @error('name')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>

    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Code</span>
        <input name="code" value="{{ old('code') }}" required class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">
        @error('code')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>

    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Timezone</span>
        <input name="timezone" value="{{ old('timezone', 'Asia/Kolkata') }}" required class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">
        @error('timezone')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>

    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Capacity</span>
        <input name="capacity" type="number" min="1" value="{{ old('capacity') }}" class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">
        @error('capacity')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>

    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Phone</span>
        <input name="phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">
        @error('phone')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>

    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Email</span>
        <input name="email" type="email" value="{{ old('email') }}" class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">
        @error('email')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>
</div>

<div class="mt-4 grid gap-4">
    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Address</span>
        <textarea name="address" rows="3" class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">{{ old('address') }}</textarea>
        @error('address')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>

    <label class="block text-sm">
        <span class="font-medium text-zinc-700">Terms summary</span>
        <textarea name="terms_summary" rows="3" class="mt-1 block w-full rounded border border-zinc-300 px-3 py-2 text-sm">{{ old('terms_summary') }}</textarea>
        @error('terms_summary')<span class="mt-1 block text-red-700">{{ $message }}</span>@enderror
    </label>
</div>
