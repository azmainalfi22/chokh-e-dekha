class EngagementManager {
    constructor() {
        this.init();
        this.setupEventListeners();
        this.addGlobalStyles();
    }

    init() {
        this.getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content;
        this.activeRequests = new Set();
        this.setupIntersectionObserver();
    }

    setupEventListeners() {
        // Like functionality with delegation
        document.addEventListener('click', (e) => {
            const likeBtn = e.target.closest('.like-btn');
            if (likeBtn && !this.isProcessing(likeBtn)) {
                e.preventDefault();
                this.toggleLike(likeBtn);
            }
        });

        // Comment toggle with delegation
        document.addEventListener('click', (e) => {
            const commentBtn = e.target.closest('.comment-btn');
            if (commentBtn) {
                e.preventDefault();
                this.toggleComments(commentBtn);
            }
        });

        // Comment form submission
        document.addEventListener('submit', (e) => {
            const form = e.target.closest('.comment-form');
            if (form) {
                e.preventDefault();
                this.submitComment(form);
            }
        });

        // Comment deletion
        document.addEventListener('click', (e) => {
            const deleteBtn = e.target.closest('.delete-comment-btn');
            if (deleteBtn) {
                e.preventDefault();
                this.deleteComment(deleteBtn);
            }
        });

        // Enhanced textarea with auto-resize and character counter
        document.addEventListener('input', (e) => {
            if (e.target.matches('.comment-form textarea')) {
                this.updateCharCounter(e.target);
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllCommentDropdowns();
            }
            // Ctrl/Cmd + Enter to submit comment
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const textarea = e.target;
                if (textarea.matches('.comment-form textarea')) {
                    const form = textarea.closest('.comment-form');
                    if (form) this.submitComment(form);
                }
            }
        });
    }

    async toggleLike(button) {
        const reportId = button.dataset.reportId;
        const isLiked = button.dataset.liked === '1';
        const likesCountEl = button.querySelector('.likes-count');
        const currentCount = parseInt(likesCountEl?.textContent) || 0;
        const icon = button.querySelector('svg');

        // Add processing state
        this.setProcessing(button, true);
        button.classList.add('processing');

        // Optimistic update with animation
        this.animateLikeButton(button, !isLiked);
        button.classList.toggle('active', !isLiked);
        button.dataset.liked = isLiked ? '0' : '1';
        
        // Animate count change
        if (likesCountEl) {
            this.animateCountChange(likesCountEl, isLiked ? currentCount - 1 : currentCount + 1);
        }
        
        // Update icon with bounce effect
        if (icon) {
            icon.style.fill = isLiked ? 'none' : 'currentColor';
            icon.style.transform = 'scale(1.2)';
            setTimeout(() => icon.style.transform = 'scale(1)', 150);
        }

        try {
            const response = await fetch(`/reports/${reportId}/like`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Sync with server response
                button.classList.toggle('active', data.liked);
                button.dataset.liked = data.liked ? '1' : '0';
                if (likesCountEl) {
                    this.animateCountChange(likesCountEl, data.likes_count);
                }
                if (icon) {
                    icon.style.fill = data.liked ? 'currentColor' : 'none';
                }
                
                // Success pulse
                this.pulseElement(button);
            } else {
                throw new Error(data.message || 'Failed to update like');
            }
        } catch (error) {
            console.error('Like error:', error);
            
            // Revert optimistic update with shake animation
            this.shakeElement(button);
            button.classList.toggle('active', isLiked);
            button.dataset.liked = isLiked ? '1' : '0';
            if (likesCountEl) {
                this.animateCountChange(likesCountEl, currentCount);
            }
            if (icon) {
                icon.style.fill = isLiked ? 'currentColor' : 'none';
            }
            
            this.showToast('Failed to update like status', 'error');
        } finally {
            this.setProcessing(button, false);
            button.classList.remove('processing');
        }
    }

    toggleComments(button) {
        const reportId = button.dataset.reportId;
        const dropdown = document.getElementById(`comments-${reportId}`);
        
        if (!dropdown) {
            console.error(`Comments dropdown not found for report ${reportId}`);
            return;
        }

        const isHidden = dropdown.classList.contains('hidden');
        
        // Close other dropdowns first
        this.closeAllCommentDropdowns(dropdown);

        if (isHidden) {
            this.openCommentsDropdown(dropdown, reportId);
            button.classList.add('active');
            button.setAttribute('aria-expanded', 'true');
        } else {
            this.closeCommentsDropdown(dropdown);
            button.classList.remove('active');
            button.setAttribute('aria-expanded', 'false');
        }
    }

    openCommentsDropdown(dropdown, reportId) {
        dropdown.classList.remove('hidden');
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'translateY(-10px)';
        
        // Trigger animation
        requestAnimationFrame(() => {
            dropdown.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            dropdown.style.opacity = '1';
            dropdown.style.transform = 'translateY(0)';
        });

        this.loadComments(reportId);
        
        // Focus textarea after animation
        setTimeout(() => {
            const textarea = dropdown.querySelector('textarea');
            if (textarea) textarea.focus();
        }, 100);
    }

    closeCommentsDropdown(dropdown) {
        dropdown.style.transition = 'all 0.2s ease-out';
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            dropdown.classList.add('hidden');
            dropdown.style.transition = '';
        }, 200);
    }

    closeAllCommentDropdowns(except = null) {
        document.querySelectorAll('.comments-dropdown').forEach(dropdown => {
            if (dropdown !== except && !dropdown.classList.contains('hidden')) {
                this.closeCommentsDropdown(dropdown);
                
                // Update button state
                const reportId = dropdown.id.replace('comments-', '');
                const button = document.querySelector(`[data-report-id="${reportId}"].comment-btn`);
                if (button) {
                    button.classList.remove('active');
                    button.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    async loadComments(reportId) {
        // Find the comments list inside the dropdown
        const dropdown = document.getElementById(`comments-${reportId}`);
        const container = dropdown?.querySelector('.comments-list');
        
        if (!container) {
            console.error(`Comments list container not found for report ${reportId}`);
            return;
        }

        // Show loading state
        container.innerHTML = '<div class="text-center py-4"><div class="animate-spin h-6 w-6 border-2 border-gray-300 border-t-blue-500 rounded-full mx-auto"></div></div>';
        
        try {
            const response = await fetch(`/reports/${reportId}/comments`, {
                headers: { 
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const html = await response.text();
            
            // Fade in new content
            container.style.opacity = '0.5';
            container.innerHTML = html;
            
            setTimeout(() => {
                container.style.transition = 'opacity 0.3s ease';
                container.style.opacity = '1';
            }, 50);
            
        } catch (error) {
            console.error('Load comments error:', error);
            container.innerHTML = '<p class="text-red-600 text-sm p-4">Failed to load comments. Please try again.</p>';
        }
    }

    async submitComment(form) {
        const reportId = form.dataset.reportId;
        const textarea = form.querySelector('textarea[name="body"]');
        const submitBtn = form.querySelector('button[type="submit"]');
        const body = textarea?.value?.trim();

        if (!body) {
            this.shakeElement(textarea);
            textarea?.focus();
            return;
        }

        // Enhanced loading state
        const originalBtnText = submitBtn.innerHTML;
        textarea.disabled = true;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4 mr-2 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"/>
                <path d="M12 2 a10 10 0 0 1 0 20" stroke="currentColor"/>
            </svg>
            Posting...
        `;

        try {
            const response = await fetch(`/reports/${reportId}/comments`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    body: body
                })
            });

            // Check response status first
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: `HTTP ${response.status}` }));
                throw new Error(errorData.message || errorData.errors?.body?.[0] || `HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.comment) {
                // Clear form
                textarea.value = '';
                this.updateCharCounter(textarea);
                
                // Find the comments list - look in the dropdown
                const dropdown = document.getElementById(`comments-${reportId}`);
                const commentsList = dropdown?.querySelector('.comments-list');
                
                if (commentsList) {
                    // If comments list is showing "no comments" message, clear it
                    const noCommentsMsg = commentsList.querySelector('.text-center');
                    if (noCommentsMsg) {
                        commentsList.innerHTML = '';
                    }
                    
                    // Add the new comment
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.comment;
                    const newComment = tempDiv.firstElementChild;
                    
                    if (newComment) {
                        // Animate in
                        newComment.style.opacity = '0';
                        newComment.style.transform = 'translateY(-20px)';
                        commentsList.insertBefore(newComment, commentsList.firstChild);
                        
                        requestAnimationFrame(() => {
                            newComment.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                            newComment.style.opacity = '1';
                            newComment.style.transform = 'translateY(0)';
                        });
                    }
                }
                
                // Update ALL comment counts for this report
                this.updateAllCommentCounts(reportId, data.comments_count);
                
                this.showToast('Comment posted successfully!', 'success');
                this.pulseElement(submitBtn);
                
            } else {
                throw new Error(data.message || 'Failed to post comment');
            }
        } catch (error) {
            console.error('Comment error:', error);
            this.shakeElement(form);
            
            // More specific error messages
            let errorMessage = 'Failed to post comment';
            if (error.message.includes('422') || error.message.includes('validation')) {
                errorMessage = 'Invalid comment. Please check your input.';
            } else if (error.message.includes('401')) {
                errorMessage = 'Please log in to post comments.';
            } else if (error.message.includes('403')) {
                errorMessage = 'You don\'t have permission to comment on this report.';
            } else if (error.message.includes('empty')) {
                errorMessage = 'Comment cannot be empty.';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            this.showToast(errorMessage, 'error', 5000);
        } finally {
            textarea.disabled = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    }

    async deleteComment(button) {
        const result = await this.showConfirmDialog('Delete Comment', 'Are you sure you want to delete this comment? This action cannot be undone.');
        if (!result) return;

        const commentId = button.dataset.commentId;
        const reportId = button.dataset.reportId;
        
        // Find the comment item - it's the parent with class 'comment-item'
        const commentItem = button.closest('.comment-item');
        
        if (!commentItem) {
            console.error('Comment item not found');
            return;
        }

        // Add loading state to button
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin h-3 w-3 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25"/>
                <path d="M12 2 a10 10 0 0 1 0 20" stroke="currentColor"/>
            </svg>
        `;

        try {
            const response = await fetch(`/reports/${reportId}/comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ message: `HTTP ${response.status}` }));
                throw new Error(errorData.message || `HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                // Animate out
                commentItem.style.transition = 'all 0.3s ease-out';
                commentItem.style.opacity = '0';
                commentItem.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    commentItem.remove();
                    
                    // Check if there are no more comments in the list
                    const dropdown = document.getElementById(`comments-${reportId}`);
                    const commentsList = dropdown?.querySelector('.comments-list');
                    
                    // Count remaining comment items
                    const remainingComments = commentsList?.querySelectorAll('.comment-item').length || 0;
                    
                    if (commentsList && remainingComments === 0) {
                        commentsList.innerHTML = `
                            <div class="text-center py-8" style="color:var(--muted, #6b7280)">
                                <svg class="h-8 w-8 mx-auto mb-2 opacity-50" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                                </svg>
                                <p class="text-sm">No comments yet. Be the first to comment!</p>
                            </div>
                        `;
                    }
                }, 300);
                
                // Update ALL comment counts for this report
                this.updateAllCommentCounts(reportId, data.comments_count);
                
                this.showToast('Comment deleted successfully', 'success');
            } else {
                throw new Error(data.message || 'Failed to delete comment');
            }
        } catch (error) {
            console.error('Delete error:', error);
            
            // Check if it's a 403 (forbidden)
            if (error.message.includes('403')) {
                this.showToast('You don\'t have permission to delete this comment', 'error');
            } else {
                this.showToast(error.message || 'Failed to delete comment', 'error');
            }
            
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    updateCharCounter(textarea) {
        const form = textarea.closest('.comment-form');
        const counter = form?.querySelector('.char-count');
        const submitBtn = form?.querySelector('button[type="submit"]');
        const length = textarea.value.length;
        const maxLength = 1000;

        if (counter) {
            counter.textContent = `${length}/${maxLength}`;
            
            // Color coding
            if (length > maxLength * 0.9) {
                counter.style.color = '#ef4444'; // red
            } else if (length > maxLength * 0.7) {
                counter.style.color = '#f59e0b'; // amber
            } else {
                counter.style.color = '#6b7280'; // gray
            }
        }

        if (submitBtn) {
            const isDisabled = length === 0 || length > maxLength;
            submitBtn.disabled = isDisabled;
            submitBtn.style.opacity = isDisabled ? '0.5' : '1';
        }

        // Auto-resize with smooth animation
        textarea.style.transition = 'height 0.1s ease';
        textarea.style.height = 'auto';
        textarea.style.height = Math.max(44, Math.min(200, textarea.scrollHeight)) + 'px';
    }

    updateCommentCount(reportId, newCount) {
        const commentBtn = document.querySelector(`[data-report-id="${reportId}"].comment-btn`);
        if (commentBtn) {
            const countEl = commentBtn.querySelector('.comments-count');
            if (countEl) {
                this.animateCountChange(countEl, newCount);
            }
            
            // Update active state based on count
            if (newCount === 0) {
                commentBtn.classList.remove('has-comments');
            } else {
                commentBtn.classList.add('has-comments');
            }
        }
    }

    // Update ALL comment counts on the page for a specific report
    updateAllCommentCounts(reportId, newCount) {
        // Update main comment button
        this.updateCommentCount(reportId, newCount);
        
        // Update any other comment count displays for this report
        document.querySelectorAll(`[data-report-id="${reportId}"] .comments-count`).forEach(el => {
            this.animateCountChange(el, newCount);
        });
        
        // Update any text that shows comment count
        document.querySelectorAll(`[data-report-comments="${reportId}"]`).forEach(el => {
            el.textContent = newCount;
        });
    }

    // Animation helpers
    animateLikeButton(button, liked) {
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
            if (liked) {
                // Heart pop animation
                const icon = button.querySelector('svg');
                if (icon) {
                    icon.style.transform = 'scale(1.3)';
                    setTimeout(() => icon.style.transform = 'scale(1)', 200);
                }
            }
        }, 100);
    }

    animateCountChange(element, newValue) {
        if (!element) return;
        element.style.transform = 'scale(1.2)';
        element.style.transition = 'transform 0.2s cubic-bezier(0.4, 0, 0.2, 1)';
        element.textContent = newValue;
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 100);
    }

    pulseElement(element) {
        if (!element) return;
        element.style.animation = 'pulse 0.6s ease-in-out';
        setTimeout(() => element.style.animation = '', 600);
    }

    shakeElement(element) {
        if (!element) return;
        element.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => element.style.animation = '', 500);
    }

    // Enhanced toast system
    showToast(message, type = 'success', duration = 3000) {
        // Remove existing toasts
        document.querySelectorAll('.engagement-toast').forEach(t => this.removeToast(t));
        
        const toast = document.createElement('div');
        toast.className = 'engagement-toast fixed left-1/2 transform -translate-x-1/2 bottom-8 z-50 px-6 py-4 rounded-xl font-medium text-white shadow-2xl backdrop-blur-sm flex items-center gap-3 max-w-md';
        
        // Icon based on type
        const icons = {
            success: `<svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>`,
            error: `<svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>`,
            info: `<svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>`
        };
        
        toast.innerHTML = `
            ${icons[type] || icons.info}
            <span class="flex-1">${message}</span>
        `;
        
        // Styling based on type
        const styles = {
            success: 'bg-gradient-to-r from-emerald-500 to-green-600',
            error: 'bg-gradient-to-r from-red-500 to-red-600',
            info: 'bg-gradient-to-r from-blue-500 to-blue-600'
        };
        
        toast.classList.add(styles[type] || styles.info);
        
        // Initial state
        toast.style.opacity = '0';
        toast.style.transform = 'translate(-50%, 20px) scale(0.9)';
        
        document.body.appendChild(toast);
        
        // Animate in
        requestAnimationFrame(() => {
            toast.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
            toast.style.opacity = '1';
            toast.style.transform = 'translate(-50%, 0) scale(1)';
        });
        
        // Auto remove
        setTimeout(() => this.removeToast(toast), duration);
    }

    removeToast(toast) {
        if (!toast) return;
        toast.style.opacity = '0';
        toast.style.transform = 'translate(-50%, -20px) scale(0.9)';
        setTimeout(() => toast.remove(), 300);
    }

    async showConfirmDialog(title, message) {
        return new Promise((resolve) => {
            const dialog = document.createElement('div');
            dialog.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm';
            dialog.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform scale-95 opacity-0 transition-all duration-300">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">${title}</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                        <div class="flex gap-3 justify-end">
                            <button class="cancel-btn px-4 py-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">Cancel</button>
                            <button class="confirm-btn px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium transition-colors">Delete</button>
                        </div>
                    </div>
                </div>
            `;

            const modal = dialog.firstElementChild;
            document.body.appendChild(dialog);

            // Animate in
            requestAnimationFrame(() => {
                modal.style.transform = 'scale(1)';
                modal.style.opacity = '1';
            });

            // Event handlers
            dialog.querySelector('.cancel-btn').onclick = () => {
                this.closeDialog(dialog);
                resolve(false);
            };
            
            dialog.querySelector('.confirm-btn').onclick = () => {
                this.closeDialog(dialog);
                resolve(true);
            };
            
            dialog.onclick = (e) => {
                if (e.target === dialog) {
                    this.closeDialog(dialog);
                    resolve(false);
                }
            };
        });
    }

    closeDialog(dialog) {
        const modal = dialog.firstElementChild;
        modal.style.transform = 'scale(0.95)';
        modal.style.opacity = '0';
        setTimeout(() => dialog.remove(), 300);
    }

    // Utility methods
    isProcessing(element) {
        return this.activeRequests.has(element);
    }

    setProcessing(element, processing) {
        if (processing) {
            this.activeRequests.add(element);
        } else {
            this.activeRequests.delete(element);
        }
    }

    setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, { threshold: 0.1 });

        // Observe engagement elements
        document.querySelectorAll('.engagement-bar').forEach(el => observer.observe(el));
    }

    addGlobalStyles() {
        if (document.getElementById('engagement-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'engagement-styles';
        style.textContent = `
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
                20%, 40%, 60%, 80% { transform: translateX(2px); }
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            .animate-fade-in {
                animation: fadeIn 0.6s ease-out;
            }
            
            .engagement-btn {
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .engagement-btn.processing {
                pointer-events: none;
                opacity: 0.8;
            }
            
            .engagement-btn:active {
                transform: scale(0.95);
            }
            
            .comment-form textarea {
                transition: height 0.1s ease, border-color 0.2s ease;
            }
            
            .comment-form textarea:focus {
                border-color: var(--accent, #3b82f6);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            
            .comments-dropdown {
                transition: all 0.3s ease;
            }
            
            .comment-btn.has-comments {
                color: var(--primary, #3b82f6);
            }
            
            .comment-item {
                transition: all 0.3s ease;
            }
        `;
        
        document.head.appendChild(style);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.engagementManager = new EngagementManager();
    console.log('Enhanced engagement system initialized');
});