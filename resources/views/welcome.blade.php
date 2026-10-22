<x-layouts.app>
    <div class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        <header class="flex flex-wrap items-center justify-between gap-4 py-5 sm:py-7">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="Gym Manager home">
                <span class="gym-mark"><x-brand-mark /></span>
                <span><span class="block text-sm font-bold tracking-wide">GYM MANAGER</span><span class="block text-xs text-zinc-500">One strong system for every gym</span></span>
            </a>
            <nav class="flex flex-wrap items-center gap-2 sm:gap-3" aria-label="Main navigation">
                <a href="#features" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-600 transition hover:bg-white hover:text-ink">Features</a>
                <a href="{{ route('login') }}" class="btn-secondary">Sign in</a>
            </nav>
        </header>

        <section class="relative grid gap-8 overflow-hidden rounded-[2rem] bg-ink px-5 py-8 text-white shadow-[0_28px_80px_rgba(20,33,30,0.18)] sm:px-8 sm:py-10 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] md:items-center md:gap-6 lg:px-12 lg:py-14">
            <div class="relative z-10 min-w-0">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.14em] text-mint"><span class="size-2 rounded-full bg-mint shadow-[0_0_0_4px_rgba(126,226,184,0.12)]"></span> Stronger gyms, smoother days</span>
                <h1 class="mt-6 max-w-2xl text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">Build a better gym, <span class="text-mint">one day at a time.</span></h1>
                <p class="mt-5 max-w-xl text-base leading-7 text-white/65 sm:text-lg">Manage members, trainers, memberships, attendance, billing, and training plans in one connected workspace.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-mint px-5 py-3 text-sm font-bold text-ink transition hover:-translate-y-0.5 hover:bg-white">Open your workspace <span aria-hidden="true">→</span></a>
                    <a href="#features" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Explore the platform</a>
                </div>
                <div class="mt-8 flex flex-wrap gap-x-5 gap-y-2 border-t border-white/10 pt-5 text-xs font-semibold text-white/55 sm:gap-x-7">
                    <span>Member journeys</span><span>Team coordination</span><span>Clear operations</span>
                </div>
            </div>

            <div class="relative min-w-0">
                <x-training-video />
                <div class="mt-4 rounded-xl border border-white/15 bg-white/5 px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-mint">Made for movement</p>
                    <p class="mt-1 text-sm font-semibold text-white">People first. Progress every day.</p>
                </div>
            </div>
        </section>

        <section id="features" class="scroll-mt-8 py-14 sm:py-20">
            <div class="mx-auto max-w-2xl text-center">
                <p class="eyebrow">One connected workspace</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Give every part of your gym a clear rhythm.</h2>
                <p class="mt-4 text-sm leading-6 text-zinc-600 sm:text-base">Spend less time switching between spreadsheets and more time helping your people make progress.</p>
            </div>
            <div class="mt-9 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="surface p-5 transition duration-200 hover:-translate-y-1 hover:shadow-lg sm:p-6"><span class="section-icon" aria-hidden="true"><x-gym-menu-icon name="members" /></span><h3 class="mt-4 font-semibold">Member journeys</h3><p class="mt-2 text-sm leading-6 text-zinc-600">Memberships, packages, payments, and personal details in one profile.</p></article>
                <article class="surface p-5 transition duration-200 hover:-translate-y-1 hover:shadow-lg sm:p-6"><span class="section-icon" aria-hidden="true"><x-gym-menu-icon name="trainers" /></span><h3 class="mt-4 font-semibold">Team coaching</h3><p class="mt-2 text-sm leading-6 text-zinc-600">Connect trainers with the members and branches they support.</p></article>
                <article class="surface p-5 transition duration-200 hover:-translate-y-1 hover:shadow-lg sm:p-6"><span class="section-icon" aria-hidden="true"><x-gym-menu-icon name="attendance" /></span><h3 class="mt-4 font-semibold">Daily operations</h3><p class="mt-2 text-sm leading-6 text-zinc-600">Keep attendance, classes, equipment, and branch activity in view.</p></article>
                <article class="surface p-5 transition duration-200 hover:-translate-y-1 hover:shadow-lg sm:p-6"><span class="section-icon" aria-hidden="true"><x-gym-menu-icon name="fitness" /></span><h3 class="mt-4 font-semibold">Personal progress</h3><p class="mt-2 text-sm leading-6 text-zinc-600">Share workout schedules and nutrition plans with each member.</p></article>
            </div>
        </section>

        <section class="flex flex-col justify-between gap-5 rounded-3xl border border-line bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:p-8">
            <div class="min-w-0"><p class="eyebrow">Ready when you are</p><h2 class="mt-2 text-2xl font-semibold">Bring your gym team together.</h2><p class="mt-2 text-sm text-zinc-600">Sign in to manage your gym workspace.</p></div>
            <a href="{{ route('login') }}" class="btn-primary shrink-0">Sign in <span aria-hidden="true" class="ml-2">→</span></a>
        </section>
        <footer class="flex flex-col gap-2 py-7 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between"><a href="{{ route('home') }}" class="font-bold tracking-wide text-ink">GYM MANAGER</a><p>Helping teams build stronger gym communities.</p></footer>
    </div>
</x-layouts.app>
