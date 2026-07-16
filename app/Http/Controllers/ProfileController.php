<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use App\Models\ReportComment;
use App\Models\ReportLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show a user's public profile
     */
    public function show(User $user): View
    {
        // Get user's reports with engagement counts
        $reports = Report::where('user_id', $user->id)
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(10);

        $reportsCount = Report::where('user_id', $user->id)->count();

        // Get recent activity
        $recentActivity = [];

        // Get recent reports
        $recentReports = Report::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentReports as $report) {
            $recentActivity[] = [
                'type' => 'report',
                'description' => 'created a report: <a href="' . route('reports.show', $report) . '" class="font-semibold text-primary hover:underline">' . $report->title . '</a>',
                'time' => $report->created_at->diffForHumans(),
                'timestamp' => $report->created_at,
            ];
        }

        // Get recent comments
        $recentComments = ReportComment::where('user_id', $user->id)
            ->with('report:id,title')
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentComments as $comment) {
            $recentActivity[] = [
                'type' => 'comment',
                'description' => 'commented on <a href="' . route('reports.show', $comment->report) . '#comment-' . $comment->id . '" class="font-semibold text-primary hover:underline">' . $comment->report->title . '</a>',
                'time' => $comment->created_at->diffForHumans(),
                'timestamp' => $comment->created_at,
            ];
        }

        // Get recent likes
        $recentLikes = ReportLike::where('user_id', $user->id)
            ->with('report:id,title')
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentLikes as $like) {
            $recentActivity[] = [
                'type' => 'like',
                'description' => 'liked <a href="' . route('reports.show', $like->report) . '" class="font-semibold text-primary hover:underline">' . $like->report->title . '</a>',
                'time' => $like->created_at->diffForHumans(),
                'timestamp' => $like->created_at,
            ];
        }

        // Sort activity by timestamp
        usort($recentActivity, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Take only the most recent 15 items
        $recentActivity = array_slice($recentActivity, 0, 15);

        // Load counts
        $user->loadCount([
            'likes as likes_given_count',
            'comments',
        ]);

        return view('profile.show', compact('user', 'reports', 'reportsCount', 'recentActivity'));
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload image if exists
        if ($request->hasFile('profile_photo')) {
            $image = $request->file('profile_photo');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/profile_photos', $filename);
            $user->profile_photo = $filename;
        }

        $user->fill($validated);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
