<?php

namespace App\Policies;

use App\Models\ReportComment;
use App\Models\User;

class ReportCommentPolicy
{
    public function destroy(User $user, ReportComment $comment): bool
    {
        // Users can delete their own comments, admins can delete any
        return $user->id === $comment->user_id || $user->is_admin;
    }

    public function view(?User $user, ReportComment $comment): bool
    {
        // Comments are public, but soft-deleted ones are only visible to admins
        if ($comment->trashed()) {
            return $user && $user->is_admin;
        }
        
        return true;
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user can comment
    }
}