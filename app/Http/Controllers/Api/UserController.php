<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q',''));
        $users = User::query()
            ->when($q !== '', fn($qb) => $qb->where('name','like',"%$q%")->orWhere('email','like',"%$q%"))
            ->latest()->paginate(15);
        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function updateStatus(Request $request, User $user)
    {
        $data = $request->validate(['is_admin' => ['required','boolean']]);
        $user->update(['is_admin' => $data['is_admin']]);
        return response()->json(['ok' => true, 'is_admin' => $user->is_admin]);
    }
}


