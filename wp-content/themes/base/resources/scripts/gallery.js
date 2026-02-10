import MasonryGallery from './components/MasonryGallery';
import GalleryAjaxLoader from './components/GalleryAjaxLoader';

/**
 * Inicjalizacja galerii masonry
 */
document.addEventListener('DOMContentLoaded', () => {
    const galleryContainer = document.querySelector('[data-gallery-container]');

    if (!galleryContainer) {
        return;
    }

    // Inicjalizacja galerii Masonry
    const masonryGallery = new MasonryGallery('[data-gallery-container]');

    // Pobieramy konfigurację z data attributes
    const config = {
        action: galleryContainer.dataset.action || 'load_gallery_items',
        nonce: galleryContainer.dataset.nonce || '',
        ajaxUrl: galleryContainer.dataset.ajaxUrl || '/wp-admin/admin-ajax.php'
    };

    // Inicjalizacja Ajax loadera
    const ajaxLoader = new GalleryAjaxLoader(masonryGallery, config);

    // Obsługa resize (opcjonalnie)
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            masonryGallery.relayout();
        }, 250);
    });
});
