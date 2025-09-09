<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Report;
use App\Models\ReportComment;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\ReportPolicy;
use App\Policies\ReportCommentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
        Report::class => ReportPolicy::class,
        ReportComment::class => ReportCommentPolicy::class, // Added for engagement system
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define custom gates for role-based permissions
        Gate::define('admin', function (User $user) {
            return $user->is_admin ?? false;
        });

        Gate::define('moderator', function (User $user) {
            return $user->hasRole(['admin', 'moderator']);
        });

        Gate::define('manage-reports', function (User $user) {
            return $user->hasRole(['admin', 'moderator']);
        });

        Gate::define('manage-comments', function (User $user) {
            return $user->hasRole(['admin', 'moderator']);
        });

        Gate::define('view-admin-dashboard', function (User $user) {
            return $user->is_admin ?? false;
        });
    }
}