<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Str;

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
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = $request->user();
        $photo = $request->file('photo');

        // Validate image dimensions
        $imageInfo = getimagesize($photo->getPathname());
        if ($imageInfo === false) {
            return Redirect::route('profile.edit')->with('error', 'Invalid image file.');
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width > 1080 || $height > 1080) {
            return Redirect::route('profile.edit')->with('error', 'Image must be maximum 1080x1080 pixels. Current size: ' . $width . 'x' . $height . ' pixels.');
        }

        // Delete old photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . $user->profile_photo);
        }

        // Store new photo
        $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
        $photo->storeAs('profile-photos', $filename, 'public');

        // Update user record
        $user->update(['profile_photo' => $filename]);

        return Redirect::route('profile.edit')->with('status', 'photo-updated');
    }

    /**
     * Delete the user's profile photo.
     */
    public function deletePhoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . $user->profile_photo);
            $user->update(['profile_photo' => null]);
        }

        return Redirect::route('profile.edit')->with('status', 'photo-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        // Delete profile photo if exists
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . $user->profile_photo);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
