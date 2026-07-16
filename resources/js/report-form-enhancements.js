/**
 * Report Form Enhancements
 * Adds visual feedback, validation, and UX improvements
 */

class ReportFormEnhancer {
    constructor() {
        this.form = document.getElementById('reportForm');
        if (!this.form) return;

        this.init();
    }

    init() {
        this.addCharacterCounters();
        this.addRealTimeValidation();
        this.addLocationQuickButton();
        this.enhanceCategorySelection();
        this.addDraftAutoSave();
        this.setupSuccessAnimation();
    }

    /**
     * Add character counters to text fields
     */
    addCharacterCounters() {
        const fields = [
            { selector: '[name="title"]', max: 255, min: 10 },
            { selector: '[name="description"]', max: 5000, min: 20 },
        ];

        fields.forEach(field => {
            const input = document.querySelector(field.selector);
            if (!input) return;

            // Create counter element
            const counter = document.createElement('div');
            counter.className = 'character-counter text-xs text-secondary mt-1 flex justify-between';
            counter.innerHTML = `
                <span class="count">0/${field.max} characters</span>
                <span class="status text-muted">Minimum: ${field.min}</span>
            `;

            input.parentElement.appendChild(counter);

            // Update counter
            input.addEventListener('input', () => {
                const length = input.value.length;
                const countSpan = counter.querySelector('.count');
                const statusSpan = counter.querySelector('.status');

                countSpan.textContent = `${length}/${field.max} characters`;

                // Color coding
                if (length < field.min) {
                    statusSpan.textContent = `${field.min - length} more needed`;
                    statusSpan.className = 'status text-red-600';
                } else if (length > field.max * 0.9) {
                    statusSpan.textContent = `${field.max - length} remaining`;
                    statusSpan.className = 'status text-orange-600';
                } else {
                    statusSpan.textContent = 'Looking good!';
                    statusSpan.className = 'status text-green-600';
                }
            });
        });
    }

    /**
     * Add real-time validation with visual feedback
     */
    addRealTimeValidation() {
        const inputs = this.form.querySelectorAll('input[required], textarea[required], select[required]');

        inputs.forEach(input => {
            const wrapper = input.closest('.form-group') || input.parentElement;

            // Create feedback icon container
            const feedbackIcon = document.createElement('div');
            feedbackIcon.className = 'validation-icon absolute right-3 top-1/2 transform -translate-y-1/2 hidden';
            feedbackIcon.innerHTML = `
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            `;

            // Make input wrapper relative
            if (wrapper) {
                wrapper.style.position = 'relative';
                wrapper.appendChild(feedbackIcon);
            }

            // Validation on blur and input
            const validate = () => {
                const isValid = this.validateInput(input);
                
                if (input.value.length > 0) {
                    if (isValid) {
                        input.classList.remove('border-red-500');
                        input.classList.add('border-green-500');
                        feedbackIcon.classList.remove('hidden');
                    } else {
                        input.classList.remove('border-green-500');
                        input.classList.add('border-red-500');
                        feedbackIcon.classList.add('hidden');
                    }
                }
            };

            input.addEventListener('blur', validate);
            input.addEventListener('input', () => {
                setTimeout(validate, 500); // Debounce
            });
        });
    }

    validateInput(input) {
        // Check if input meets requirements
        if (input.required && !input.value.trim()) {
            return false;
        }

        if (input.minLength && input.value.length < input.minLength) {
            return false;
        }

        if (input.maxLength && input.value.length > input.maxLength) {
            return false;
        }

        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(input.value);
        }

        return true;
    }

    /**
     * Add "Use My Location" quick button
     */
    addLocationQuickButton() {
        const locationInput = document.querySelector('[name="location"]');
        if (!locationInput) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-outline btn-sm mt-2';
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            </svg>
            Use My Location
        `;

        button.addEventListener('click', () => this.useMyLocation(button));
        locationInput.parentElement.appendChild(button);
    }

    useMyLocation(button) {
        const originalHTML = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Detecting...
        `;

        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            button.disabled = false;
            button.innerHTML = originalHTML;
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                
                // Update hidden fields if they exist
                const latInput = document.querySelector('[name="latitude"]');
                const lngInput = document.querySelector('[name="longitude"]');
                if (latInput) latInput.value = latitude;
                if (lngInput) lngInput.value = longitude;

                // Update map if available (EnhancedReportForm)
                if (window.enhancedReportForm && window.enhancedReportForm.map) {
                    window.enhancedReportForm.map.setCenter({ lat: latitude, lng: longitude });
                    if (window.enhancedReportForm.marker) {
                        window.enhancedReportForm.marker.setPosition({ lat: latitude, lng: longitude });
                    }
                }

                // Reverse geocode
                this.reverseGeocode(latitude, longitude);

                button.disabled = false;
                button.innerHTML = `
                    <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Location Set!
                `;

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                }, 3000);
            },
            (error) => {
                console.error('Geolocation error:', error);
                alert('Could not detect your location. Please enter it manually or check your browser permissions.');
                button.disabled = false;
                button.innerHTML = originalHTML;
            }
        );
    }

    reverseGeocode(lat, lng) {
        if (window.google && window.google.maps) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const locationInput = document.querySelector('[name="location"]');
                    const addressInput = document.querySelector('[name="formatted_address"]');
                    
                    if (locationInput) locationInput.value = results[0].formatted_address;
                    if (addressInput) addressInput.value = results[0].formatted_address;
                }
            });
        }
    }

    /**
     * Enhance category selection with icons
     */
    enhanceCategorySelection() {
        const categorySelect = document.querySelector('[name="category"]');
        if (!categorySelect) return;

        const categories = {
            infrastructure: { icon: '🏗️', label: 'Infrastructure', color: 'blue' },
            sanitation: { icon: '🚽', label: 'Sanitation', color: 'green' },
            safety: { icon: '🚨', label: 'Safety', color: 'red' },
            corruption: { icon: '💰', label: 'Corruption', color: 'purple' },
            environment: { icon: '🌳', label: 'Environment', color: 'emerald' },
            health: { icon: '🏥', label: 'Health', color: 'pink' },
            traffic: { icon: '🚦', label: 'Traffic', color: 'orange' },
            noise: { icon: '📢', label: 'Noise', color: 'yellow' },
            other: { icon: '⚙️', label: 'Other', color: 'gray' },
        };

        // Update options with icons
        Array.from(categorySelect.options).forEach(option => {
            const key = option.value;
            if (categories[key]) {
                option.textContent = `${categories[key].icon} ${categories[key].label}`;
            }
        });

        // Show selected category with larger icon
        const displayDiv = document.createElement('div');
        displayDiv.className = 'selected-category-display mt-2 p-4 rounded-lg border-2 border-dashed border-gray-300 text-center hidden';
        categorySelect.parentElement.appendChild(displayDiv);

        categorySelect.addEventListener('change', () => {
            const selected = categorySelect.value;
            if (selected && categories[selected]) {
                const cat = categories[selected];
                displayDiv.className = `selected-category-display mt-2 p-4 rounded-lg border-2 border-${cat.color}-300 bg-${cat.color}-50 text-center`;
                displayDiv.innerHTML = `
                    <div class="text-5xl mb-2">${cat.icon}</div>
                    <div class="font-semibold text-${cat.color}-800">${cat.label}</div>
                `;
            } else {
                displayDiv.classList.add('hidden');
            }
        });
    }

    /**
     * Add draft auto-save functionality
     */
    addDraftAutoSave() {
        const DRAFT_KEY = 'report_draft';
        let saveTimeout;

        // Load draft on page load
        const draft = localStorage.getItem(DRAFT_KEY);
        if (draft) {
            try {
                const data = JSON.parse(draft);
                if (confirm('You have a saved draft. Would you like to restore it?')) {
                    this.restoreDraft(data);
                }
            } catch (e) {
                console.error('Error loading draft:', e);
            }
        }

        // Save draft on input
        this.form.addEventListener('input', () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                this.saveDraft(DRAFT_KEY);
            }, 2000); // Save 2 seconds after last input
        });

        // Clear draft on successful submission
        this.form.addEventListener('submit', () => {
            setTimeout(() => {
                localStorage.removeItem(DRAFT_KEY);
            }, 1000);
        });
    }

    saveDraft(key) {
        const formData = new FormData(this.form);
        const data = {};
        
        formData.forEach((value, key) => {
            if (key !== '_token' && key !== 'photo' && key !== 'photos') {
                data[key] = value;
            }
        });

        localStorage.setItem(key, JSON.stringify(data));
        this.showDraftSavedNotification();
    }

    restoreDraft(data) {
        Object.keys(data).forEach(key => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = data[key];
                
                // Trigger validation
                input.dispatchEvent(new Event('input'));
            }
        });
    }

    showDraftSavedNotification() {
        // Create or update notification
        let notification = document.getElementById('draft-saved-notification');
        
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'draft-saved-notification';
            notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg text-sm flex items-center gap-2 z-50';
            notification.innerHTML = `
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Draft saved</span>
            `;
            document.body.appendChild(notification);
        }

        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';

        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(10px)';
        }, 2000);
    }

    /**
     * Setup success animation
     */
    setupSuccessAnimation() {
        // This will be triggered after successful submission
        window.showReportSuccess = (reportId, trackingId) => {
            // Create confetti
            if (window.confetti) {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }

            // Show success modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 animate-fade-in';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md mx-4 animate-scale-in">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce">
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Report Submitted!</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            Thank you for helping improve our community. Your report has been received and will be reviewed shortly.
                        </p>
                        ${trackingId ? `
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 mb-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Tracking ID</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">#${trackingId}</p>
                            </div>
                        ` : ''}
                        <div class="flex gap-3">
                            <a href="/reports/${reportId}" class="flex-1 btn btn-primary">
                                View Report
                            </a>
                            <a href="/reports" class="flex-1 btn btn-outline">
                                Browse Reports
                            </a>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Remove modal on click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        };
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.reportFormEnhancer = new ReportFormEnhancer();
    });
} else {
    window.reportFormEnhancer = new ReportFormEnhancer();
}

export default ReportFormEnhancer;

