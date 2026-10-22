<x-layouts.app>
    <div>
        <p class="eyebrow">Member portal</p>
        <h1 class="mt-2 text-3xl font-semibold">Welcome, {{ $member->name }}</h1>
        <p class="mt-2 text-sm text-zinc-600">Your gym: <span class="font-semibold">{{ $member->homeBranch?->name ?? 'Not assigned' }}</span> · Trainer: <span class="font-semibold">{{ $member->trainerProfile?->name ?? $member->trainer?->name ?? 'Not assigned' }}</span>@if($member->trainerProfile?->specialization) · {{ $member->trainerProfile->specialization }}@endif</p>
        @if ($member->trainerProfile?->email || $member->trainerProfile?->phone)<p class="mt-1 text-sm text-zinc-500">Trainer contact: {{ $member->trainerProfile?->email }}{{ $member->trainerProfile?->phone ? ' · '.$member->trainerProfile->phone : '' }}</p>@endif
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-2">
        <section class="surface p-6">
            <p class="eyebrow">Personal training</p>
            <h2 class="mt-1 text-xl font-semibold">Your workouts and goals</h2>
            <div class="mt-4 space-y-4">
                @forelse ($member->workoutAssignments as $workout)
                    <article class="rounded border border-zinc-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-semibold">{{ $workout->name }}</h3><span class="text-xs text-zinc-500">Plan v{{ $workout->version }} · {{ $workout->starts_on->format('d M Y') }}</span></div>
                        @if ($workout->weekly_schedule)
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($workoutDays as $day => $label)
                                    @if (! empty($workout->weekly_schedule[$day]))
                                        <div class="rounded bg-paper p-3">
                                            <h4 class="text-sm font-semibold">{{ $label }}</h4>
                                            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-zinc-700">
                                                @foreach ($workout->weekly_schedule[$day] as $exercise)
                                                    <li>{{ $exercise }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-zinc-700">
                                @foreach ($workout->exercises ?? [] as $exercise)
                                    <li>{{ $exercise }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if ($workout->instructions)<p class="mt-3 whitespace-pre-line text-sm text-zinc-600">{{ $workout->instructions }}</p>@endif
                    </article>
                @empty
                    <p class="text-sm text-zinc-500">Your trainer has not added a workout plan yet.</p>
                @endforelse
                <details class="rounded border border-zinc-200 p-4">
                    <summary class="cursor-pointer font-semibold">Create or update my weekly plan</summary>
                    <p class="mt-2 text-sm text-zinc-500">Saving adds a new version and replaces the active plan. Add exercises on training days and leave rest days blank.</p>
                    <form method="POST" action="{{ route('fitness.workouts.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <input type="hidden" name="member_id" value="{{ $member->id }}">
                        <input name="name" required maxlength="255" value="{{ old('name', $member->workoutAssignments->first()?->name ?? 'My weekly workout') }}" placeholder="Program name" class="rounded border-zinc-300">
                        <input type="date" name="starts_on" required value="{{ old('starts_on', now()->toDateString()) }}" class="rounded border-zinc-300">
                        <input type="date" name="ends_on" value="{{ old('ends_on') }}" aria-label="Optional end date" class="rounded border-zinc-300">
                        <textarea name="instructions" maxlength="3000" placeholder="Notes for your trainer" class="rounded border-zinc-300">{{ old('instructions') }}</textarea>
                        <div class="grid gap-3 sm:col-span-2 sm:grid-cols-2">
                            @foreach ($workoutDays as $day => $label)
                                <label class="text-sm font-medium">{{ $label }}
                                    <textarea name="weekly_schedule[{{ $day }}]" rows="3" placeholder="One exercise per line" class="mt-1 w-full rounded border-zinc-300">{{ old('weekly_schedule.'.$day) }}</textarea>
                                </label>
                            @endforeach
                        </div>
                        <button class="w-fit rounded bg-emerald-700 px-4 py-2 text-sm font-medium text-white sm:col-span-2">Save my workout plan</button>
                    </form>
                </details>
                @foreach ($member->fitnessGoals as $goal)
                    <article class="rounded bg-paper p-4"><p class="font-semibold">{{ $goal->title }}</p><p class="mt-1 text-sm text-zinc-600">{{ $goal->category }} · Started {{ $goal->starts_on->format('d M Y') }}@if ($goal->target_on) · Target {{ $goal->target_on->format('d M Y') }}@endif</p>@if ($goal->notes)<p class="mt-2 whitespace-pre-line text-sm text-zinc-600">{{ $goal->notes }}</p>@endif</article>
                @endforeach
            </div>
        </section>

        <section class="surface p-6">
            <p class="eyebrow">Nutrition</p>
            <h2 class="mt-1 text-xl font-semibold">Your diet plans</h2>
            <div class="mt-4 space-y-4">
                @forelse ($member->mealPlans as $mealPlan)
                    <article class="rounded border border-zinc-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-semibold">{{ $mealPlan->name }}</h3><span class="text-xs text-zinc-500">Plan v{{ $mealPlan->version }} · From {{ $mealPlan->starts_on->format('d M Y') }}</span></div>
                        <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-zinc-700">@foreach ($mealPlan->meals ?? [] as $meal)<li>{{ $meal }}</li>@endforeach</ul>
                        @if ($mealPlan->preferences['notes'] ?? null)<p class="mt-3 text-sm text-zinc-600">Notes: {{ $mealPlan->preferences['notes'] }}</p>@endif
                    </article>
                @empty
                    <p class="text-sm text-zinc-500">Your trainer has not added a diet plan yet.</p>
                @endforelse
            </div>
        </section>

        <section class="surface p-6">
            <p class="eyebrow">Membership</p>
            <h2 class="mt-1 text-xl font-semibold">Your package</h2>
            <div class="mt-4 space-y-3 text-sm">
                @forelse ($member->memberships as $membership)
                    <article class="border-b border-zinc-100 pb-3">
                        <p class="font-semibold">{{ $membership->plan_snapshot['name'] ?? $membership->plan?->name ?? 'Membership package' }}</p>
                        <p class="mt-1 text-zinc-600">
                            {{ $membership->starts_on->format('d M Y') }} to {{ $membership->ends_on->format('d M Y') }}
                            · {{ ucfirst(str_replace('_', ' ', $membership->status)) }}
                        </p>
                        <p class="mt-1 text-zinc-500">
                            Package total: ₹{{ number_format($membership->total_paise / 100, 2) }}
                            @if ($membership->sessions_total !== null)
                                · {{ $membership->remainingSessions() }} of {{ $membership->sessions_total }} sessions remaining
                            @endif
                        </p>
                        @if ($membership->notes)
                            <p class="mt-2 whitespace-pre-line text-zinc-500">{{ $membership->notes }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-zinc-500">No memberships found.</p>
                @endforelse
            </div>
        </section>

        <section class="surface p-6">
            <p class="eyebrow">Billing</p>
            <h2 class="mt-1 text-xl font-semibold">Upcoming payments</h2>
            <p class="mt-2 text-sm text-zinc-600">Outstanding balance: <span class="font-semibold">₹{{ number_format($outstandingPaise / 100, 2) }}</span></p>
            <div class="mt-4 space-y-3 text-sm">@foreach($upcomingInstallments as $installment)<article class="flex flex-wrap items-center justify-between gap-3 rounded border border-amber-200 bg-amber-50 p-4"><div><p class="font-semibold">Installment {{ $installment->sequence }}</p><p class="mt-1 text-xs text-zinc-600">Invoice {{ $invoices->firstWhere('id', $installment->invoice_id)?->number }} · Due {{ $installment->due_on->format('d M Y') }}</p></div><p class="font-semibold">₹{{ number_format($installment->amount_paise / 100, 2) }}</p></article>@endforeach @foreach($upcomingInvoices as $invoice)<article class="flex flex-wrap items-center justify-between gap-3 rounded border border-amber-200 bg-amber-50 p-4"><div><p class="font-semibold">{{ $invoice->lines->pluck('description')->filter()->implode(', ') ?: 'Gym invoice' }}</p><p class="mt-1 text-xs text-zinc-600">Invoice {{ $invoice->number }} · Due {{ $invoice->due_on->format('d M Y') }}</p></div><p class="font-semibold">₹{{ number_format($invoice->balancePaise() / 100, 2) }}</p></article>@endforeach @if($upcomingInvoices->isEmpty() && $upcomingInstallments->isEmpty())<p class="text-sm text-zinc-500">No upcoming payments recorded.</p>@endif</div>
            @if($overdueInvoices->isNotEmpty() || $overdueInstallments->isNotEmpty())<div class="mt-5 border-t border-line pt-4"><h3 class="font-semibold text-rose-800">Overdue</h3><div class="mt-3 space-y-2 text-sm">@foreach($overdueInstallments as $installment)<p class="flex justify-between gap-3"><span>Installment {{ $installment->sequence }} · due {{ $installment->due_on->format('d M Y') }}</span><span class="font-semibold">₹{{ number_format($installment->amount_paise / 100, 2) }}</span></p>@endforeach @foreach($overdueInvoices as $invoice)<p class="flex justify-between gap-3"><span>{{ $invoice->number }} · due {{ $invoice->due_on->format('d M Y') }}</span><span class="font-semibold">₹{{ number_format($invoice->balancePaise() / 100, 2) }}</span></p>@endforeach</div></div>@endif
        </section>

        <section class="surface p-6">
            <p class="eyebrow">Payment history</p>
            <h2 class="mt-1 text-xl font-semibold">Payments received</h2>
            <div class="mt-4 divide-y divide-zinc-100 text-sm">@forelse($payments as $payment)<article class="flex flex-wrap items-center justify-between gap-3 py-3"><div><p class="font-semibold">₹{{ number_format($payment->amount_paise / 100, 2) }} · {{ ucfirst(str_replace('_', ' ', $payment->method)) }}</p><p class="mt-1 text-xs text-zinc-500">{{ $payment->received_on->format('d M Y') }}@if($payment->provider_reference) · Ref {{ $payment->provider_reference }}@endif</p><p class="mt-1 text-xs text-zinc-500">Invoices: {{ $payment->allocations->pluck('invoice.number')->filter()->implode(', ') ?: 'Payment recorded to member account' }}</p></div>@if($payment->refunds->isNotEmpty())<span class="text-xs text-rose-700">Refunded ₹{{ number_format($payment->refunds->where('status', 'refunded')->sum('amount_paise') / 100, 2) }}</span>@endif</article>@empty<p class="py-4 text-zinc-500">No payments recorded.</p>@endforelse</div>
            <div class="mt-5 border-t border-line pt-4"><h3 class="font-semibold">Invoice history</h3><div class="mt-3 divide-y divide-zinc-100">@forelse($invoices as $invoice)<p class="flex flex-wrap justify-between gap-2 py-2"><span>{{ $invoice->number }} · {{ $invoice->issued_on?->format('d M Y') ?? 'Date not set' }} · {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span><span class="font-medium">Paid ₹{{ number_format($invoice->paid_paise / 100, 2) }} / ₹{{ number_format($invoice->total_paise / 100, 2) }}</span></p>@empty<p class="py-3 text-zinc-500">No invoices recorded.</p>@endforelse</div></div>
        </section>

        <section class="surface p-6">
            <p class="eyebrow">Gym contacts</p>
            <h2 class="mt-1 text-xl font-semibold">Your gym and staff</h2>
            @if($member->homeBranch)<div class="mt-4 rounded bg-paper p-4"><p class="font-semibold">{{ $member->homeBranch->name }}</p><p class="mt-1 text-sm text-zinc-600">{{ collect([$member->homeBranch->address, $member->homeBranch->city, $member->homeBranch->state, $member->homeBranch->postal_code])->filter()->implode(', ') }}</p><p class="mt-2 space-x-3 text-sm">@if($member->homeBranch->phone)<a class="text-emerald-800 hover:underline" href="tel:{{ $member->homeBranch->phone }}">{{ $member->homeBranch->phone }}</a>@endif @if($member->homeBranch->email)<a class="text-emerald-800 hover:underline" href="mailto:{{ $member->homeBranch->email }}">{{ $member->homeBranch->email }}</a>@endif</p></div>@endif
            <div class="mt-4 divide-y divide-zinc-100">@forelse($staff as $contact)<article class="py-3"><p class="font-semibold">{{ $contact['name'] }}</p><p class="mt-1 text-xs text-zinc-500">{{ $contact['roles'] }}</p><p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">@if($contact['phone'])<a class="text-emerald-800 hover:underline" href="tel:{{ $contact['phone'] }}">{{ $contact['phone'] }}</a>@endif @if($contact['email'])<a class="text-emerald-800 hover:underline" href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>@endif</p></article>@empty<p class="py-3 text-sm text-zinc-500">No additional gym contacts are listed.</p>@endforelse</div>
        </section>

        <section class="surface p-6">
            <p class="eyebrow">Attendance</p>
            <h2 class="mt-1 text-xl font-semibold">Recent visits</h2>
            <div class="mt-4 space-y-2 text-sm">@forelse ($member->attendanceVisits->take(20) as $visit)<p class="flex justify-between gap-3"><span>{{ $visit->branch->name }}</span><span class="text-zinc-500">{{ $visit->checked_in_at->format('d M Y, H:i') }}</span></p>@empty<p class="text-zinc-500">No visits found.</p>@endforelse</div>
        </section>
    </div>
</x-layouts.app>
