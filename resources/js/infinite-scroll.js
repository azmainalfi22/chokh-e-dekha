/**
 * Infinite Scroll Implementation
 * Progressively loads reports as user scrolls
 */

class InfiniteScroll {
    constructor() {
        this.loading = false;
        this.hasMore = true;
        this.currentPage = 1;
        this.container = null;
        this.loader = null;
        this.observer = null;
        
        this.init();
    }

    init() {
        // Find the feed container
        this.container = document.querySelector('.feed-container');
        if (!this.container) return;

        // Check if pagination exists
        const pagination = document.querySelector('.pagination');
        if (!pagination) {
            this.hasMore = false;
            return;
        }

        // Get initial page from URL
        const urlParams = new URLSearchParams(window.location.search);
        this.currentPage = parseInt(urlParams.get('page') || '1');

        // Create loader element
        this.createLoader();

        // Setup intersection observer
        this.setupObserver();

        // Hide original pagination
        if (pagination) {
            pagination.style.display = 'none';
        }
    }

    createLoader() {
        this.loader = document.createElement('div');
        this.loader.className = 'infinite-scroll-loader';
        this.loader.innerHTML = `
            <div class="flex items-center justify-center py-8">
                <div class="flex items-center gap-3">
                    <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-secondary font-medium">Loading more reports...</span>
                </div>
            </div>
        `;
        this.loader.style.display = 'none';
        
        // Insert after feed container
        if (this.container.parentNode) {
            this.container.parentNode.insertBefore(this.loader, this.container.nextSibling);
        }
    }

    setupObserver() {
        const options = {
            root: null,
            rootMargin: '200px', // Start loading 200px before reaching bottom
            threshold: 0.1
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.loading && this.hasMore) {
                    this.loadMore();
                }
            });
        }, options);

        // Observe the loader
        if (this.loader) {
            this.observer.observe(this.loader);
        }
    }

    async loadMore() {
        if (this.loading || !this.hasMore) return;

        this.loading = true;
        this.loader.style.display = 'block';

        try {
            // Build URL with current filters + next page
            const url = new URL(window.location.href);
            url.searchParams.set('page', this.currentPage + 1);
            
            const response = await fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load more reports');
            }

            const html = await response.text();
            
            // Parse response to extract reports
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newReports = doc.querySelectorAll('.feed-container > article.feed-card');
            
            if (newReports.length === 0) {
                // No more reports
                this.hasMore = false;
                this.showEndMessage();
            } else {
                // Append new reports
                newReports.forEach((report, index) => {
                    // Add staggered animation
                    report.style.opacity = '0';
                    report.style.transform = 'translateY(20px)';
                    this.container.appendChild(report);
                    
                    // Animate in
                    setTimeout(() => {
                        report.style.transition = 'all 0.3s ease';
                        report.style.opacity = '1';
                        report.style.transform = 'translateY(0)';
                    }, index * 50);
                });

                this.currentPage++;
                
                // Check if there's a next page
                const nextPageLink = doc.querySelector('.pagination a[rel="next"]');
                if (!nextPageLink) {
                    this.hasMore = false;
                    this.showEndMessage();
                }
            }

        } catch (error) {
            console.error('Infinite scroll error:', error);
            this.showError();
        } finally {
            this.loading = false;
            this.loader.style.display = 'none';
        }
    }

    showEndMessage() {
        if (this.loader) {
            this.loader.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <svg class="h-12 w-12 mx-auto mb-3 text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-secondary font-medium">You've reached the end!</p>
                        <p class="text-muted text-sm mt-1">No more reports to show</p>
                    </div>
                </div>
            `;
            this.loader.style.display = 'block';
            
            // Stop observing
            if (this.observer) {
                this.observer.disconnect();
            }
        }
    }

    showError() {
        if (this.loader) {
            this.loader.innerHTML = `
                <div class="flex items-center justify-center py-8">
                    <div class="text-center">
                        <svg class="h-12 w-12 mx-auto mb-3 text-red-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-secondary font-medium">Failed to load more reports</p>
                        <button onclick="window.infiniteScroll.loadMore()" class="btn btn-primary btn-sm mt-3">
                            Try Again
                        </button>
                    </div>
                </div>
            `;
            this.loader.style.display = 'block';
        }
    }

    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        if (this.loader) {
            this.loader.remove();
        }
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.infiniteScroll = new InfiniteScroll();
    });
} else {
    window.infiniteScroll = new InfiniteScroll();
}

export default InfiniteScroll;

