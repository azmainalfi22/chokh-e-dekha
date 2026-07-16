/**
 * Quick Actions Menu Handler
 * Handles bookmark, edit, delete actions
 */

class QuickActions {
    constructor() {
        this.init();
    }

    init() {
        this.setupBookmarkHandlers();
    }

    /**
     * Setup bookmark handlers
     */
    setupBookmarkHandlers() {
        document.addEventListener('click', async (e) => {
            if (!e.target.closest('.bookmark-report')) return;
            
            e.preventDefault();
            const button = e.target.closest('.bookmark-report');
            const reportId = button.dataset.reportId;
            
            try {
                const response = await fetch(`/reports/${reportId}/bookmark`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Update button text
                    const svg = button.querySelector('svg');
                    const text = button.querySelector('span') || button;
                    
                    if (data.bookmarked) {
                        if (svg) svg.setAttribute('fill', 'currentColor');
                        if (text.textContent) text.textContent = 'Saved';
                    } else {
                        if (svg) svg.setAttribute('fill', 'none');
                        if (text.textContent) text.textContent = 'Save Report';
                    }

                    // Show toast if available
                    if (window.socialInteractions) {
                        window.socialInteractions.showToast(
                            data.bookmarked ? 'Report saved' : 'Report unsaved',
                            'success'
                        );
                    }
                } else {
                    throw new Error(data.message || 'Failed to bookmark');
                }
            } catch (error) {
                console.error('Bookmark error:', error);
                if (window.socialInteractions) {
                    window.socialInteractions.showToast('Failed to save report', 'error');
                }
            }
        });
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.quickActions = new QuickActions();
    });
} else {
    window.quickActions = new QuickActions();
}

export default QuickActions;

