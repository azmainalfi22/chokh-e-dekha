/**
 * Photo Gallery Lightbox
 * Beautiful image viewer with zoom, navigation, and thumbnails
 */

class PhotoGallery {
    constructor() {
        this.currentIndex = 0;
        this.images = [];
        this.lightbox = null;
        this.init();
    }

    init() {
        this.createLightbox();
        this.attachEventListeners();
    }

    createLightbox() {
        this.lightbox = document.createElement('div');
        this.lightbox.id = 'photo-lightbox';
        this.lightbox.className = 'fixed inset-0 bg-black bg-opacity-95 z-[9999] hidden flex items-center justify-center';
        this.lightbox.innerHTML = `
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <!-- Close Button -->
                <button class="absolute top-4 right-4 z-50 w-12 h-12 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all" onclick="window.photoGallery.close()">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <!-- Previous Button -->
                <button class="absolute left-4 top-1/2 transform -translate-y-1/2 z-50 w-12 h-12 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all" onclick="window.photoGallery.prev()">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <!-- Next Button -->
                <button class="absolute right-4 top-1/2 transform -translate-y-1/2 z-50 w-12 h-12 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full flex items-center justify-center transition-all" onclick="window.photoGallery.next()">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Main Image -->
                <div class="relative max-w-7xl max-h-full">
                    <img id="lightbox-image" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl" alt="">
                    
                    <!-- Image Counter -->
                    <div class="absolute top-4 left-4 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-sm font-medium">
                        <span id="current-index">1</span> / <span id="total-images">1</span>
                    </div>

                    <!-- Zoom Controls -->
                    <div class="absolute bottom-4 right-4 flex gap-2">
                        <button onclick="window.photoGallery.zoomIn()" class="w-10 h-10 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                            </svg>
                        </button>
                        <button onclick="window.photoGallery.zoomOut()" class="w-10 h-10 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                            </svg>
                        </button>
                        <button onclick="window.photoGallery.resetZoom()" class="w-10 h-10 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full flex items-center justify-center transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Thumbnails -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 overflow-x-auto max-w-full p-2" id="lightbox-thumbnails">
                    <!-- Thumbnails will be added dynamically -->
                </div>
            </div>
        `;

        document.body.appendChild(this.lightbox);
    }

    attachEventListeners() {
        // Click on gallery images to open lightbox
        document.addEventListener('click', (e) => {
            const galleryImage = e.target.closest('[data-gallery-image]');
            if (galleryImage) {
                e.preventDefault();
                const images = document.querySelectorAll('[data-gallery-image]');
                this.images = Array.from(images).map(img => ({
                    src: img.getAttribute('data-full-src') || img.src,
                    thumb: img.src,
                    alt: img.alt || 'Report photo'
                }));
                
                const index = Array.from(images).indexOf(galleryImage);
                this.open(index);
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (!this.lightbox.classList.contains('hidden')) {
                if (e.key === 'Escape') this.close();
                if (e.key === 'ArrowLeft') this.prev();
                if (e.key === 'ArrowRight') this.next();
            }
        });

        // Click backdrop to close
        this.lightbox.addEventListener('click', (e) => {
            if (e.target === this.lightbox || e.target.closest('#photo-lightbox > div') === this.lightbox.firstElementChild) {
                this.close();
            }
        });
    }

    open(index = 0) {
        this.currentIndex = index;
        this.lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        this.updateImage();
        this.updateThumbnails();
    }

    close() {
        this.lightbox.classList.add('hidden');
        document.body.style.overflow = '';
        this.resetZoom();
    }

    next() {
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this.updateImage();
    }

    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.updateImage();
    }

    updateImage() {
        const image = document.getElementById('lightbox-image');
        const currentIndexEl = document.getElementById('current-index');
        const totalImagesEl = document.getElementById('total-images');

        if (this.images[this.currentIndex]) {
            image.src = this.images[this.currentIndex].src;
            image.alt = this.images[this.currentIndex].alt;
        }

        currentIndexEl.textContent = this.currentIndex + 1;
        totalImagesEl.textContent = this.images.length;

        this.updateThumbnails();
        this.resetZoom();
    }

    updateThumbnails() {
        const container = document.getElementById('lightbox-thumbnails');
        container.innerHTML = '';

        this.images.forEach((img, index) => {
            const thumb = document.createElement('button');
            thumb.className = `w-16 h-16 rounded-lg overflow-hidden border-2 transition-all ${
                index === this.currentIndex ? 'border-white scale-110' : 'border-transparent opacity-60 hover:opacity-100'
            }`;
            thumb.innerHTML = `<img src="${img.thumb}" alt="${img.alt}" class="w-full h-full object-cover">`;
            thumb.onclick = () => {
                this.currentIndex = index;
                this.updateImage();
            };
            container.appendChild(thumb);
        });
    }

    zoomIn() {
        const image = document.getElementById('lightbox-image');
        const currentScale = parseFloat(image.style.transform?.match(/scale\(([\d.]+)\)/)?.[1] || 1);
        const newScale = Math.min(currentScale * 1.2, 3);
        image.style.transform = `scale(${newScale})`;
        image.style.cursor = 'zoom-out';
    }

    zoomOut() {
        const image = document.getElementById('lightbox-image');
        const currentScale = parseFloat(image.style.transform?.match(/scale\(([\d.]+)\)/)?.[1] || 1);
        const newScale = Math.max(currentScale / 1.2, 1);
        image.style.transform = `scale(${newScale})`;
        if (newScale === 1) {
            image.style.cursor = 'zoom-in';
        }
    }

    resetZoom() {
        const image = document.getElementById('lightbox-image');
        image.style.transform = 'scale(1)';
        image.style.cursor = 'zoom-in';
    }
}

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.photoGallery = new PhotoGallery();
    });
} else {
    window.photoGallery = new PhotoGallery();
}

export default PhotoGallery;

