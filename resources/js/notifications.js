/**
 * Real-time Notifications System
 * Polls for new notifications and updates the UI
 */

class NotificationSystem {
    constructor() {
        this.pollingInterval = null;
        this.pollDelay = 30000; // 30 seconds
        this.isPolling = false;
        this.unreadCount = 0;
        
        this.init();
    }

    init() {
        // Don't start polling if user is not authenticated
        if (!document.querySelector('meta[name="csrf-token"]')) {
            return;
        }

        // Start polling
        this.startPolling();

        // Setup event listeners
        this.setupEventListeners();

        // Initial load
        this.poll();
    }

    /**
     * Start polling for notifications
     */
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollingInterval = setInterval(() => {
            this.poll();
        }, this.pollDelay);
    }

    /**
     * Stop polling for notifications
     */
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
        this.isPolling = false;
    }

    /**
     * Poll for new notifications
     */
    async poll() {
        try {
            const response = await fetch('/notifications/poll', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to poll notifications');
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateUnreadCount(data.unread_count);
                
                // If there are new notifications, optionally show a toast
                if (data.has_new && data.latest) {
                    this.showNewNotificationToast(data.latest);
                }
            }
        } catch (error) {
            console.error('Notification polling error:', error);
            // Don't show error to user, just log it
        }
    }

    /**
     * Update unread count badge
     */
    updateUnreadCount(count) {
        this.unreadCount = count;
        
        // Update all notification badges
        const badges = document.querySelectorAll('[data-notification-badge]');
        badges.forEach(badge => {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                badge.classList.add('animate-bounce');
                setTimeout(() => badge.classList.remove('animate-bounce'), 600);
            } else {
                badge.classList.add('hidden');
            }
        });

        // Update notification icon
        const icons = document.querySelectorAll('[data-notification-icon]');
        icons.forEach(icon => {
            if (count > 0) {
                icon.classList.add('text-primary');
                icon.classList.remove('text-secondary');
            } else {
                icon.classList.add('text-secondary');
                icon.classList.remove('text-primary');
            }
        });
    }

    /**
     * Show toast for new notification
     */
    showNewNotificationToast(notification) {
        // Only show toast if not on notifications page
        if (window.location.pathname.includes('/notifications')) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = 'fixed top-20 right-4 max-w-sm z-50 animate-slide-in';
        toast.innerHTML = `
            <div class="card shadow-xl border-l-4 border-primary">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm mb-1">${notification.title || 'New Notification'}</p>
                        <p class="text-xs text-secondary line-clamp-2">${notification.message || ''}</p>
                        <div class="mt-2 flex gap-2">
                            ${notification.report_id ? `<a href="/reports/${notification.report_id}" class="text-xs text-primary hover:underline font-medium">View Report</a>` : ''}
                            <button onclick="this.closest('.fixed').remove()" class="text-xs text-secondary hover:text-primary font-medium">Dismiss</button>
                        </div>
                    </div>
                    <button onclick="this.closest('.fixed').remove()" class="flex-shrink-0 text-secondary hover:text-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto remove after 10 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 10000);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // Pause polling when page is hidden
                this.stopPolling();
            } else {
                // Resume polling when page is visible
                this.startPolling();
                this.poll(); // Immediate poll on return
            }
        });

        // Listen for focus/blur events
        window.addEventListener('focus', () => {
            if (!this.isPolling) {
                this.startPolling();
                this.poll();
            }
        });

        window.addEventListener('blur', () => {
            // Keep polling even when blurred, but could reduce frequency
        });

        // Listen for online/offline events
        window.addEventListener('online', () => {
            if (!this.isPolling) {
                this.startPolling();
                this.poll();
            }
        });

        window.addEventListener('offline', () => {
            this.stopPolling();
        });
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to mark notification as read');
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateUnreadCount(data.unread_count);
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/notifications/mark-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to mark all notifications as read');
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateUnreadCount(0);
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            return false;
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.notificationSystem = new NotificationSystem();
    });
} else {
    window.notificationSystem = new NotificationSystem();
}

export default NotificationSystem;

