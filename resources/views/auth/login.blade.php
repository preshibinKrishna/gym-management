<x-layouts.app>
    <div class="login-page mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl items-center gap-8 py-4 lg:grid-cols-[minmax(0,1fr)_400px] lg:gap-14">
        <section class="login-hero">
            <div class="login-hero-top"><div class="flex items-center gap-3"><span class="gym-mark"><x-brand-mark /></span><span class="text-sm font-bold tracking-wide text-white">GYM MANAGER</span></div><span class="login-live-pill"><i></i> Training floor live</span></div>
            <div class="login-hero-copy"><p class="eyebrow text-mint">Multi-owner gym operations</p><h1 class="mt-3 max-w-2xl text-4xl font-semibold text-white sm:text-6xl">Train harder.<br><span class="text-mint">Manage smarter.</span></h1><p class="mt-5 max-w-lg text-base leading-7 text-white/65">Your complete command centre for memberships, people, attendance, finance, fitness, and daily operations.</p></div>
            <div class="login-visual-stage" aria-label="Male and female trainers exercising in a gym"><span class="login-stage-grid"></span><span class="login-stage-kicker">GYM LIFE / 05 MOVEMENTS</span><div class="login-exercise-frame"><img src="{{ asset('images/gym-duo-exercise-pushup.png') }}" alt="" aria-hidden="true"><img src="{{ asset('images/gym-duo-exercise-standing.png') }}" alt="" aria-hidden="true"><img src="{{ asset('images/gym-duo-exercise-running.png') }}" alt="" aria-hidden="true"><img src="{{ asset('images/gym-duo-exercise-chest-press.png') }}" alt="" aria-hidden="true"><img src="{{ asset('images/gym-duo-exercise-dumbbell-curl.png') }}" alt="" aria-hidden="true"></div><div class="login-exercise-labels" aria-hidden="true"><span>Push-ups</span><span>Reset</span><span>Run</span><span>Chest press</span><span>Dumbbell curls</span></div></div>
            <div class="login-hero-footer"><span>Strength</span><span>Cardio</span><span>Performance</span><span class="hidden sm:inline">Built for the modern gym</span></div>
        </section>

        <section class="surface p-6 sm:p-8">
            <p class="eyebrow">Secure access</p><h2 class="mt-2 text-2xl font-semibold">Welcome back</h2><p class="mt-1 text-sm text-zinc-500">Sign in to your operations workspace.</p>
            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="field" placeholder="you@company.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-700">Password</label>
                    <input id="password" name="password" type="password" required class="field" placeholder="Your password">
                    @error('password')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-emerald-700 focus:ring-emerald-600">
                    Remember this device
                </label>

                <button type="submit" class="btn-primary w-full">Sign in <span aria-hidden="true" class="ml-2">→</span></button>
            </form>

            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                Demo access: <span class="font-semibold">owner.apex@example.test</span><br><span class="text-amber-800">Password: DevOnly!2026</span>
            </div>
        </section>
    </div>
</x-layouts.app>
