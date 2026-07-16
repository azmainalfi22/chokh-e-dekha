/**
 * Advanced Search with Live Suggestions
 * Provides real-time search suggestions and advanced filtering
 */

class SearchEnhancements {
    constructor() {
        this.searchInput = null;
        this.suggestionsContainer = null;
        this.debounceTimer = null;
        this.minChars = 2;
        this.currentFocus = -1;
        this.init();
    }

    init() {
        this.searchInput = document.querySelector('input[name="q"]');
        if (!this.searchInput) return;

        this.createSuggestionsContainer();
        this.attachEventListeners();
    }

    createSuggestionsContainer() {
        this.suggestionsContainer = document.createElement('div');
        this.suggestionsContainer.className = 'search-suggestions';
        this.suggestionsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            margin-top: 0.5rem;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            z-index: 100;
        `;

        // Wrap search input in relative container
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        this.searchInput.parentNode.insertBefore(wrapper, this.searchInput);
        wrapper.appendChild(this.searchInput);
        wrapper.appendChild(this.suggestionsContainer);
    }

    attachEventListeners() {
        // Search input
        this.searchInput.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            const query = e.target.value.trim();

            if (query.length < this.minChars) {
                this.hideSuggestions();
                return;
            }

            this.debounceTimer = setTimeout(() => {
                this.fetchSuggestions(query);
            }, 300);
        });

        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            const items = this.suggestionsContainer.querySelectorAll('.suggestion-item');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                this.currentFocus++;
                this.setActive(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                this.currentFocus--;
                this.setActive(items);
            } else if (e.key === 'Enter' && this.currentFocus > -1) {
                e.preventDefault();
                if (items[this.currentFocus]) {
                    items[this.currentFocus].click();
                }
            } else if (e.key === 'Escape') {
                this.hideSuggestions();
            }
        });

        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-suggestions') && e.target !== this.searchInput) {
                this.hideSuggestions();
            }
        });

        // Focus to show recent searches
        this.searchInput.addEventListener('focus', () => {
            if (this.searchInput.value.length >= this.minChars) {
                this.fetchSuggestions(this.searchInput.value);
            } else {
                this.showRecentSearches();
            }
        });
    }

    async fetchSuggestions(query) {
        try {
            // In a real implementation, this would call an API endpoint
            // For now, we'll use local filtering of categories and statuses
            const suggestions = this.getLocalSuggestions(query);
            this.displaySuggestions(suggestions, query);
        } catch (error) {
            console.error('Failed to fetch suggestions:', error);
        }
    }

    getLocalSuggestions(query) {
        const lowerQuery = query.toLowerCase();
        const suggestions = [];

        // Get categories from the page
        const categorySelect = document.querySelector('select[name="category"]');
        if (categorySelect) {
            Array.from(categorySelect.options).forEach(option => {
                if (option.value && option.textContent.toLowerCase().includes(lowerQuery)) {
                    suggestions.push({
                        type: 'category',
                        value: option.value,
                        label: option.textContent,
                        icon: this.getCategoryIcon(option.value)
                    });
                }
            });
        }

        // Get cities
        const citySelect = document.querySelector('select[name="city_corporation"]');
        if (citySelect) {
            Array.from(citySelect.options).forEach(option => {
                if (option.value && option.textContent.toLowerCase().includes(lowerQuery)) {
                    suggestions.push({
                        type: 'city',
                        value: option.value,
                        label: option.textContent,
                        icon: '📍'
                    });
                }
            });
        }

        // Get statuses
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            Array.from(statusSelect.options).forEach(option => {
                if (option.value && option.textContent.toLowerCase().includes(lowerQuery)) {
                    suggestions.push({
                        type: 'status',
                        value: option.value,
                        label: option.textContent,
                        icon: this.getStatusIcon(option.value)
                    });
                }
            });
        }

        // Add recent searches if no results
        if (suggestions.length === 0) {
            const recent = this.getRecentSearches().filter(term => 
                term.toLowerCase().includes(lowerQuery)
            );
            recent.forEach(term => {
                suggestions.push({
                    type: 'recent',
                    value: term,
                    label: term,
                    icon: '🕒'
                });
            });
        }

        return suggestions.slice(0, 8);
    }

    getCategoryIcon(category) {
        const icons = {
            'infrastructure': '🏗️',
            'sanitation': '🚿',
            'roads': '🛣️',
            'water': '💧',
            'electricity': '⚡',
            'pollution': '🏭',
            'garbage': '🗑️',
            'traffic': '🚦',
            'healthcare': '🏥',
            'education': '📚',
            'safety': '🛡️',
            'other': '📋'
        };
        return icons[category] || '📝';
    }

    getStatusIcon(status) {
        const icons = {
            'pending': '⏳',
            'in_progress': '⚙️',
            'resolved': '✅',
            'rejected': '❌'
        };
        return icons[status] || '📊';
    }

    displaySuggestions(suggestions, query) {
        if (suggestions.length === 0) {
            this.suggestionsContainer.innerHTML = `
                <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.875rem;">
                    <svg style="width: 3rem; height: 3rem; margin: 0 auto 0.5rem; opacity: 0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No suggestions found</p>
                    <p style="font-size: 0.75rem; margin-top: 0.25rem;">Try searching for "${query}"</p>
                    <button onclick="document.querySelector('input[name=q]').closest('form').submit()" 
                            class="btn btn-primary btn-sm" style="margin-top: 0.75rem;">
                        Search Reports
                    </button>
                </div>
            `;
            this.showSuggestions();
            return;
        }

        const html = suggestions.map((item, index) => `
            <div class="suggestion-item ${index === 0 ? 'active' : ''}" 
                 data-type="${item.type}" 
                 data-value="${item.value}"
                 style="padding: 0.75rem 1rem; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; border-bottom: 1px solid var(--border-light);">
                <span style="font-size: 1.25rem; flex-shrink: 0;">${item.icon}</span>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 500; color: var(--text-primary); font-size: 0.875rem;">${this.highlightMatch(item.label, query)}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: capitalize;">${item.type}</div>
                </div>
                <svg style="width: 1rem; height: 1rem; color: var(--text-muted); opacity: 0; transition: opacity 0.2s;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        `).join('');

        this.suggestionsContainer.innerHTML = html;
        this.attachSuggestionListeners();
        this.showSuggestions();
    }

    highlightMatch(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span style="background: var(--color-secondary-light); padding: 0 0.125rem; border-radius: 0.25rem; font-weight: 600;">$1</span>');
    }

    attachSuggestionListeners() {
        const items = this.suggestionsContainer.querySelectorAll('.suggestion-item');
        items.forEach(item => {
            item.addEventListener('mouseenter', () => {
                items.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                item.querySelector('svg').style.opacity = '1';
            });

            item.addEventListener('mouseleave', () => {
                item.querySelector('svg').style.opacity = '0';
            });

            item.addEventListener('click', () => {
                const type = item.dataset.type;
                const value = item.dataset.value;

                if (type === 'recent') {
                    this.searchInput.value = value;
                    this.searchInput.closest('form').submit();
                } else {
                    // Fill the appropriate filter
                    const select = document.querySelector(`select[name="${type === 'city' ? 'city_corporation' : type}"]`);
                    if (select) {
                        select.value = value;
                        this.searchInput.value = '';
                        this.searchInput.closest('form').submit();
                    }
                }

                this.saveRecentSearch(value);
                this.hideSuggestions();
            });
        });
    }

    setActive(items) {
        if (!items.length) return;
        
        if (this.currentFocus >= items.length) this.currentFocus = 0;
        if (this.currentFocus < 0) this.currentFocus = items.length - 1;

        items.forEach((item, index) => {
            if (index === this.currentFocus) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    showRecentSearches() {
        const recent = this.getRecentSearches();
        if (recent.length === 0) return;

        const html = `
            <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border-light);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Recent Searches</span>
                    <button onclick="window.searchEnhancements.clearRecentSearches()" 
                            style="font-size: 0.75rem; color: var(--color-primary); background: none; border: none; cursor: pointer; padding: 0.25rem 0.5rem;">
                        Clear All
                    </button>
                </div>
            </div>
            ${recent.map(term => `
                <div class="suggestion-item" data-type="recent" data-value="${term}"
                     style="padding: 0.75rem 1rem; cursor: pointer; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; border-bottom: 1px solid var(--border-light);">
                    <span style="font-size: 1.25rem;">🕒</span>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 500; color: var(--text-primary); font-size: 0.875rem;">${term}</div>
                    </div>
                    <svg style="width: 1rem; height: 1rem; color: var(--text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            `).join('')}
        `;

        this.suggestionsContainer.innerHTML = html;
        this.attachSuggestionListeners();
        this.showSuggestions();
    }

    getRecentSearches() {
        try {
            return JSON.parse(localStorage.getItem('recentSearches') || '[]');
        } catch {
            return [];
        }
    }

    saveRecentSearch(term) {
        if (!term || term.length < 2) return;
        
        let recent = this.getRecentSearches();
        recent = recent.filter(t => t !== term);
        recent.unshift(term);
        recent = recent.slice(0, 5);
        
        try {
            localStorage.setItem('recentSearches', JSON.stringify(recent));
        } catch (error) {
            console.error('Failed to save recent search:', error);
        }
    }

    clearRecentSearches() {
        try {
            localStorage.removeItem('recentSearches');
            this.hideSuggestions();
        } catch (error) {
            console.error('Failed to clear recent searches:', error);
        }
    }

    showSuggestions() {
        this.suggestionsContainer.style.display = 'block';
        this.currentFocus = -1;
    }

    hideSuggestions() {
        this.suggestionsContainer.style.display = 'none';
        this.currentFocus = -1;
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.searchEnhancements = new SearchEnhancements();
    });
} else {
    window.searchEnhancements = new SearchEnhancements();
}

export default SearchEnhancements;

