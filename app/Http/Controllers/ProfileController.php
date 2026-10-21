<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isStudent()) {
            $user->name = trim($request->validated('first_name').' '.$request->validated('last_name'));
            $user->preferred_name = $request->validated('preferred_name') ?: $request->validated('first_name');
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isStudent() && ($user->portfolioIsLocked() || $user->canOnlySubmitEvidence())) {
            throw ValidationException::withMessages(['password' => 'Je kunt je account niet verwijderen zolang je portfolio aan deze groepsstatus is gekoppeld.']);
        }

        if ($user->isAdmin()) {
            throw ValidationException::withMessages(['password' => 'Een adminaccount kan niet via het eigen profiel worden verwijderd.']);
        }

        if ($user->isStudent() && $user->portfolios()->whereHas('evidence', fn ($query) => $query->where('status', 'akkoord'))->exists()) {
            throw ValidationException::withMessages(['password' => 'Je account kan niet worden verwijderd zolang er goedgekeurde bewijsstukken in je portfolio staan.']);
        }

        foreach ($user->portfolios()->with('evidence')->get() as $portfolio) {
            foreach ($portfolio->evidence as $evidence) {
                if ($evidence->file_path) {
                    Storage::disk('local')->delete($evidence->file_path);
                    Storage::disk('public')->delete($evidence->file_path);
                }
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
