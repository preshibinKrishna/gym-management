<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $this->establishInitialContext($request->user());

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function establishInitialContext(User $user): void
    {
        $affiliation = $user->affiliations()
            ->where('status', 'active')
            ->with('organization')
            ->orderBy('organization_id')
            ->first();

        if ($affiliation === null) {
            return;
        }

        $branch = Branch::query()
            ->whereBelongsTo($affiliation->organization)
            ->where(function ($query) use ($user, $affiliation): void {
                if ($user->hasOrganizationWideRole($affiliation->organization)) {
                    return;
                }

                $query->whereHas('assignments', fn ($assignment) => $assignment
                    ->where('user_id', $user->id)
                    ->where('status', 'active'));
            })
            ->orderBy('name')
            ->first();

        session([
            'tenant.organization_id' => $affiliation->organization_id,
            'tenant.branch_id' => $branch?->id,
        ]);
    }
}
