<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    public function edit(Request $request)
    {
        // extra safety: ensure it's an admin
        abort_unless($request->user() && $request->user()->is_admin, 403);

        $admin = $request->user();
        return view('admin.profile.edit', compact('admin'));
    }

    public function update(Request $request)
    {
        abort_unless($request->user() && $request->user()->is_admin, 403);

        $admin = $request->user();

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($admin->id)],
            'password'      => ['nullable', 'confirmed', 'min:8'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Handle profile photo
        if ($request->hasFile('profile_photo')) {
            if ($admin->profile_photo) {
                Storage::disk('public')->delete('profile_photos/' . $admin->profile_photo);
            }

            // Store with hashed filename under /profile_photos
            $path = $request->file('profile_photo')->store('profile_photos', 'public');

            // Save only the filename (basename) for consistency with your blades
            $validated['profile_photo'] = basename($path);
        }

        // Only hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $admin->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
