<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = request()->user()->load(['roleAssignments.role', 'roleAssignments.branch', 'affiliations.organization']);

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('avatar');
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }
}
