<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Gym Management') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell {{ request()->routeIs('dashboard', 'members.*', 'home') ? 'motion-gym' : '' }} {{ request()->routeIs('login') ? 'login-gym-bg' : '' }}" x-data="{ sidebarOpen: false, quickActionsOpen: false }">
        @auth
        @php
            $isPlatformSuperAdmin = auth()->user()->is_platform_super_admin;
        @endphp
        <div x-show="sidebarOpen" x-cloak x-on:click="sidebarOpen = false" class="fixed inset-0 z-20 bg-ink/45 lg:hidden"></div>
        <aside class="app-sidebar" :class="sidebarOpen ? 'is-open' : ''">
            <div class="flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="gym-mark"><x-brand-mark /></span>
                    <span><span class="block text-sm font-bold tracking-wide">{{ $isPlatformSuperAdmin ? 'PLATFORM ADMIN' : 'GYM MANAGER' }}</span><span class="block text-xs text-white/45">{{ $isPlatformSuperAdmin ? 'Gym onboarding' : 'Operations workspace' }}</span></span>
                </a>
                <button type="button" x-on:click="sidebarOpen = false" class="rounded-lg p-2 text-white/60 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Close navigation">X</button>
            </div>
            <div class="mt-8 rounded-xl border border-white/10 bg-white/5 p-3"><p class="text-xs uppercase text-white/40">Workspace</p><p class="mt-1 truncate text-sm font-semibold">{{ app(\App\Support\Tenancy\TenantContext::class)->organization()?->name ?? 'Platform administration' }}</p><p class="mt-1 truncate text-xs text-white/50">{{ app(\App\Support\Tenancy\TenantContext::class)->branch()?->name ?? 'All branches' }}</p></div>
            <nav class="mt-7 flex flex-1 flex-col gap-1 overflow-y-auto">
                <p class="mb-2 px-3 text-xs font-semibold uppercase text-white/35">Workspace</p>
                @php
                    $tenantOrganizationId = app(\App\Support\Tenancy\TenantContext::class)->organizationId();
                    $isMemberAccount = ! $isPlatformSuperAdmin
                        && $tenantOrganizationId !== null
                        && auth()->user()->memberProfiles()->where('organization_id', $tenantOrganizationId)->exists()
                        && ! auth()->user()->roleAssignments()->where('organization_id', $tenantOrganizationId)->whereHas('role', fn ($query) => $query->where('key', 'trainer'))->exists();
                @endphp
                @php
                    if ($isPlatformSuperAdmin) {
                        $navigationItems = [['dashboard', 'Dashboard', 'dashboard'], ['platform.gym-enquiries.index', 'Gym enquiries', 'branches']];
                    } elseif ($isMemberAccount) {
                        $navigationItems = [['member-portal', 'My gym and training', 'fitness']];
                    } else {
                        $navigationItems = [['dashboard', 'Dashboard', 'dashboard'], ['branches.index', 'Branches', 'branches'], ['leads.index', 'Member leads', 'leads'], ['members.index', 'Members', 'members'], ['trainers.index', 'Trainers', 'trainers'], ['plans.index', 'Plans', 'plans'], ['batches.index', 'Batches', 'batches'], ['attendance.index', 'Attendance', 'attendance'], ['finance.index', 'Finance', 'finance'], ['reports.finance', 'Reports', 'reports'], ['fitness.index', 'Fitness', 'fitness'], ['operations.index', 'Operations', 'operations']];
                    }
                @endphp
                @foreach ($navigationItems as [$routeName, $label, $icon])
                    @if($routeName !== 'leads.index' || auth()->user()->hasPermission('leads.manage', app(\App\Support\Tenancy\TenantContext::class)->organization(), app(\App\Support\Tenancy\TenantContext::class)->branch()))
                    @php
                        $isActive = request()->routeIs($routeName, $icon.'.*');
                    @endphp
                    <a href="{{ route($routeName) }}" @if($isActive) aria-current="page" @endif class="nav-item {{ $isActive ? 'nav-item-active' : '' }}"><span class="grid size-7 place-items-center rounded-lg bg-white/10 {{ $isActive ? 'bg-ink/10' : '' }}" title="{{ $label }}"><x-gym-menu-icon :name="$icon" /></span><span>{{ $label }}</span></a>
                    @endif
                @endforeach
            </nav>
            <div class="border-t border-white/10 pt-4"><div class="flex items-center gap-3"><a href="{{ route('profile.edit') }}" class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-full bg-white/15 text-sm font-bold hover:ring-2 hover:ring-mint">@if(auth()->user()?->avatar_path)<img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="size-full object-cover">@else{{ str(auth()->user()?->name ?? 'U')->substr(0, 1)->upper() }}@endif</a><div class="min-w-0 flex-1"><a href="{{ route('profile.edit') }}" class="block truncate text-sm font-semibold hover:text-mint">{{ auth()->user()?->name }}</a><p class="truncate text-xs text-white/45">{{ auth()->user()?->email }}</p></div></div></div>
        </aside>
        <div class="app-main">
            <header class="app-topbar"><div class="flex items-center gap-3"><button type="button" x-on:click="sidebarOpen = true" class="rounded-xl border border-line bg-white p-2.5 text-sm font-bold lg:hidden" aria-label="Open navigation">Menu</button><div><p class="hidden text-xs font-semibold uppercase text-emerald-700 sm:block">{{ now()->format('l, d M Y') }}</p><p class="text-sm font-semibold lg:hidden">{{ $isPlatformSuperAdmin ? 'Platform Admin' : ($isMemberAccount ? 'Member portal' : 'Gym Manager') }}</p></div></div><div class="flex items-center gap-3">@if(! $isPlatformSuperAdmin && ! $isMemberAccount)<button type="button" x-on:click="quickActionsOpen = true" class="btn-primary hidden sm:inline-flex">+ Quick add</button>@endif<span class="hidden rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-800 md:inline-flex">System healthy</span><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rounded-lg border border-line bg-white px-3 py-2 text-xs font-semibold text-zinc-700 transition hover:border-emerald-300 hover:text-emerald-800" aria-label="Log out">Log out</button></form><a href="{{ route('profile.edit') }}" class="grid size-10 place-items-center overflow-hidden rounded-full bg-ink text-sm font-bold text-mint ring-2 ring-transparent transition hover:ring-emerald-300">@if(auth()->user()?->avatar_path)<img src="{{ asset('storage/'.auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="size-full object-cover">@else{{ str(auth()->user()?->name ?? 'U')->substr(0, 1)->upper() }}@endif</a></div></header>
            @if(! $isPlatformSuperAdmin && ! $isMemberAccount)
            <div x-show="quickActionsOpen" x-cloak x-on:keydown.escape.window="quickActionsOpen = false" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="quick-actions-title">
                <div x-on:click.outside="quickActionsOpen = false" class="modal-panel">
                    <div class="flex items-start justify-between gap-4"><div><p class="eyebrow">Shortcuts</p><h2 id="quick-actions-title" class="mt-1 text-xl font-semibold">Quick actions</h2></div><button type="button" x-on:click="quickActionsOpen = false" class="rounded-lg px-2 py-1 text-lg text-zinc-500 hover:bg-zinc-100" aria-label="Close quick actions">x</button></div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ([['members.create', 'Add member', 'Create a member record'], ['leads.create', 'Add lead', 'Capture a new enquiry'], ['plans.create', 'Create plan', 'Set up a membership plan'], ['batches.create', 'Create batch', 'Schedule a group class']] as [$routeName, $label, $description])
                            <a href="{{ route($routeName) }}" class="rounded-xl border border-line p-4 transition hover:-translate-y-0.5 hover:border-emerald-400 hover:bg-emerald-50"><span class="grid size-8 place-items-center rounded-lg bg-emerald-100 font-bold text-emerald-800">+</span><span class="mt-3 block text-sm font-semibold">{{ $label }}</span><span class="mt-1 block text-xs text-zinc-500">{{ $description }}</span></a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            <main class="mx-auto max-w-[1480px] px-4 py-7 sm:px-8 sm:py-9">
                @if (session('status'))<div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900"><span class="grid size-6 place-items-center rounded-full bg-emerald-600 text-xs text-white">OK</span><span class="flex-1">{{ session('status') }}</span><button type="button" x-on:click="show = false" class="rounded-lg px-2 py-1 text-emerald-800 hover:bg-emerald-100" aria-label="Dismiss notification">x</button></div>@endif
                @if(request()->routeIs('dashboard'))
                    @include('components.gym-motion')
                @endif
                {{ $slot }}
            </main>
        </div>
        @else
            @if(request()->routeIs('home'))
                <main class="min-h-screen">{{ $slot }}</main>
            @else
                <main class="guest-shell px-4 py-8 sm:px-8">{{ $slot }}</main>
            @endif
        @endauth
    </body>
</html>
