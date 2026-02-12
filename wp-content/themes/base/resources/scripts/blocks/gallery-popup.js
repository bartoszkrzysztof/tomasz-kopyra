import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';

/**
 * Gallery Block - Popup with Swiper
 * Initialization per block instance
 */
class GalleryPopup {
    constructor(blockElement) {
        this.block = blockElement;
        this.popup = this.block.querySelector('[data-gallery-popup]');
        this.openButtons = this.block.querySelectorAll('[data-gallery-open]');
        this.closeButtons = this.popup.querySelectorAll('[data-gallery-close]');
        
        this.mainSwiper = null;
        this.swiperInitialized = false;
        
        this.init();
    }

    init() {
        // Bind events only - Swiper will be initialized on first popup open
        this.bindEvents();
    }

    initSwipers() {
        // Main swiper
        const mainElement = this.popup.querySelector('.gallery-popup__main');
        if (mainElement) {
            this.mainSwiper = new Swiper(mainElement, {
                modules: [Navigation],
                navigation: {
                    nextEl: this.popup.querySelector('.-next'),
                    prevEl: this.popup.querySelector('.-prev'),
                },
                keyboard: {
                    enabled: true,
                },
            });
        }
    }

    bindEvents() {
        // Open popup on thumbnail click
        this.openButtons.forEach(button => {

            button.addEventListener('click', (e) => {
                const slideIndex = parseInt(button.dataset.galleryOpen, 10);
                this.openPopup(slideIndex);
            });
        });

        // Close popup
        this.closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                this.closePopup();
            });
        });

        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.popup.classList.contains('is-open')) {
                this.closePopup();
            }
        });
    }

    openPopup(slideIndex = 0) {
        this.popup.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        
        // Wait for CSS transition and rendering
        setTimeout(() => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                console.log('Initializing Swiper for gallery popup');
                if (slideIndex) {
                    console.log('Opening slide index:', slideIndex);
                }
                if (!this.swiperInitialized) {
                    // this.initSwipers();
                    this.swiperInitialized = true;
                }
            });
        });
        }, 300);
    }

    closePopup() {
        this.popup.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    destroy() {
        if (this.mainSwiper) {
            this.mainSwiper.destroy();
        }
    }
}

/**
 * Initialize all gallery blocks on page
 */
function initGalleryBlocks() {
    const galleryBlocks = document.querySelectorAll('.gallery-block');
    
    galleryBlocks.forEach(block => {
        // Check if already initialized
        if (block.dataset.galleryInitialized) {
            return;
        }
        
        // Mark as initialized
        block.dataset.galleryInitialized = 'true';
        
        // Create instance
        new GalleryPopup(block);
    });
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGalleryBlocks);
} else {
    initGalleryBlocks();
}

// Export for manual initialization if needed
export default GalleryPopup;