/**
 * Social Interactions Module
 * Handles like, comment, and share functionality
 */

class SocialInteractions {
    constructor() {
        this.init();
    }

    init() {
        this.setupLikeButtons();
        this.setupCommentButtons();
        this.setupShareButtons();
    }

    /**
     * Setup like functionality
     */
    setupLikeButtons() {
        document.addEventListener('click', async (e) => {
            const likeBtn = e.target.closest('[data-action="like"]');
            if (!likeBtn) return;

            const reportId = likeBtn.dataset.reportId;
            if (!reportId) return;

            try {
                likeBtn.disabled = true;
                const isLiked = likeBtn.classList.contains('liked');

                const response = await fetch(`/reports/${reportId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to update like');
                }

                const data = await response.json();

                // Update UI
                if (data.liked) {
                    likeBtn.classList.add('liked');
                    likeBtn.querySelector('svg').setAttribute('fill', 'currentColor');
                } else {
                    likeBtn.classList.remove('liked');
                    likeBtn.querySelector('svg').setAttribute('fill', 'none');
                }

                // Update like count
                this.updateLikeCount(reportId, data.likes_count);

                // Show toast
                this.showToast(data.liked ? 'Liked' : 'Unliked', 'success');

            } catch (error) {
                console.error('Like error:', error);
                this.showToast('Failed to update like', 'error');
            } finally {
                likeBtn.disabled = false;
            }
        });
    }

    /**
     * Setup comment functionality
     */
    setupCommentButtons() {
        document.addEventListener('click', (e) => {
            const commentBtn = e.target.closest('[data-action="comment"]');
            if (!commentBtn) return;

            const reportId = commentBtn.dataset.reportId;
            if (!reportId) return;

            // Focus comment input or redirect to detail page
            const commentSection = document.querySelector(`#comments-${reportId}`);
            if (commentSection) {
                commentSection.classList.toggle('hidden');
                commentSection.querySelector('textarea')?.focus();
            } else {
                // Redirect to report detail page
                window.location.href = `/reports/${reportId}#comments`;
            }
        });
    }

    /**
     * Setup share functionality
     */
    setupShareButtons() {
        document.addEventListener('click', async (e) => {
            const shareBtn = e.target.closest('[data-action="share"]');
            if (!shareBtn) return;

            const reportId = shareBtn.dataset.reportId;
            const title = shareBtn.dataset.title || 'Community Report';
            const url = `${window.location.origin}/reports/${reportId}`;

            // Track share
            this.trackShare(reportId);

            // Try Web Share API first (mobile)
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: title,
                        text: 'Check out this community report',
                        url: url,
                    });
                    this.showToast('Shared successfully', 'success');
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Share error:', error);
                    }
                }
            } else {
                // Fallback: Copy to clipboard
                try {
                    await navigator.clipboard.writeText(url);
                    this.showToast('Link copied to clipboard', 'success');
                    
                    // Show share modal
                    this.showShareModal(title, url, reportId);
                } catch (error) {
                    console.error('Copy error:', error);
                    this.showToast('Failed to copy link', 'error');
                }
            }
        });
    }

    /**
     * Track share action
     */
    async trackShare(reportId) {
        try {
            await fetch(`/reports/${reportId}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
            });
        } catch (error) {
            // Fail silently, share tracking is not critical
            console.log('Share tracking failed:', error);
        }
    }

    /**
     * Update like count in UI
     */
    updateLikeCount(reportId, count) {
        const statsElement = document.querySelector(`[data-report-id="${reportId}"] .post-stats`);
        if (!statsElement) return;

        const likesElement = statsElement.querySelector('[data-likes]');
        if (likesElement) {
            likesElement.textContent = count;
            likesElement.closest('div').style.display = count > 0 ? 'flex' : 'none';
        }
    }

    /**
     * Show share modal
     */
    showShareModal(title, url, reportId) {
        const modal = document.createElement('div');
        modal.className = 'share-modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4';
        modal.style.cssText = 'background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(5px);';
        modal.innerHTML = `
            <div class="share-modal-content bg-[var(--card-bg)] rounded-2xl p-0 max-w-lg w-full shadow-2xl animate-fade-in-up overflow-hidden border border-[var(--border-light)]">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-[var(--border-light)]">
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                        Share Report
                    </h3>
                    <button onclick="this.closest('.share-modal-overlay').remove()" 
                            class="w-8 h-8 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6">
                    <p class="text-sm text-secondary mb-6">Share this report with your network</p>
                    
                    <!-- Social Media Platforms Grid -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <!-- Facebook -->
                        <button onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}', '_blank', 'width=600,height=400'); window.socialInteractions.trackShare(${reportId})" 
                                class="share-platform-btn group">
                            <div class="w-12 h-12 rounded-xl bg-[#1877F2] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium">Facebook</span>
                        </button>

                        <!-- Twitter -->
                        <button onclick="window.open('https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}', '_blank', 'width=600,height=400'); window.socialInteractions.trackShare(${reportId})" 
                                class="share-platform-btn group">
                            <div class="w-12 h-12 rounded-xl bg-[#1DA1F2] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium">Twitter</span>
                        </button>

                        <!-- WhatsApp -->
                        <button onclick="window.open('https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}', '_blank'); window.socialInteractions.trackShare(${reportId})" 
                                class="share-platform-btn group">
                            <div class="w-12 h-12 rounded-xl bg-[#25D366] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium">WhatsApp</span>
                        </button>

                        <!-- LinkedIn -->
                        <button onclick="window.open('https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}', '_blank', 'width=600,height=400'); window.socialInteractions.trackShare(${reportId})" 
                                class="share-platform-btn group">
                            <div class="w-12 h-12 rounded-xl bg-[#0A66C2] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium">LinkedIn</span>
                        </button>

                        <!-- Telegram -->
                        <button onclick="window.open('https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}', '_blank'); window.socialInteractions.trackShare(${reportId})" 
                                class="share-platform-btn group">
                            <div class="w-12 h-12 rounded-xl bg-[#0088CC] flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium">Telegram</span>
                        </button>

                        <!-- Email -->
                        <button onclick="window.location.href='mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent('Check out this report: ' + url)}'; window.socialInteractions.trackShare(${reportId})" 
                                class="share-platform-btn group">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-600 to-gray-800 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium">Email</span>
                        </button>
                    </div>
                    
                    <!-- Copy Link Section -->
                    <div class="pt-4 border-t border-[var(--border-light)]">
                        <label class="text-xs font-bold uppercase tracking-wider text-muted mb-3 block">Copy Link</label>
                        <div class="flex gap-2">
                            <input type="text" value="${url}" readonly 
                                   class="flex-1 px-4 py-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 border border-[var(--border-light)] text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary" 
                                   id="share-url-input-${reportId}"
                                   onclick="this.select()">
                            <button onclick="navigator.clipboard.writeText('${url}').then(() => { window.socialInteractions.showToast('Link copied to clipboard!', 'success'); window.socialInteractions.trackShare(${reportId}); this.innerHTML='<svg class=\\'w-5 h-5\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M5 13l4 4L19 7\\'/></svg> Copied'; setTimeout(() => this.innerHTML='<svg class=\\'w-5 h-5\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><path stroke-linecap=\\'round\\' stroke-linejoin=\\'round\\' stroke-width=\\'2\\' d=\\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\\'/></svg> Copy', 2000); })" 
                                    class="px-4 py-2.5 rounded-lg bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold text-sm transition-all flex items-center gap-2 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        
        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.opacity = '0';
                setTimeout(() => modal.remove(), 200);
            }
        });

        // Close on Escape key
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                modal.style.opacity = '0';
                setTimeout(() => modal.remove(), 200);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = 'toast-notification fixed top-20 right-4 px-6 py-3 rounded-lg shadow-xl z-50 animate-slide-in';
        
        const colors = {
            success: 'bg-gradient-to-r from-emerald-500 to-cyan-500 text-white',
            error: 'bg-gradient-to-r from-red-500 to-pink-500 text-white',
            info: 'bg-gradient-to-r from-blue-500 to-purple-500 text-white',
        };

        toast.className += ' ' + (colors[type] || colors.info);
        toast.textContent = message;

        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.socialInteractions = new SocialInteractions();
    });
} else {
    window.socialInteractions = new SocialInteractions();
}

export default SocialInteractions;

