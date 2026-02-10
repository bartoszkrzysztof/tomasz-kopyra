/**
 * GalleryAjaxLoader - Klasa obsługująca ajaxowe doładowywanie elementów galerii
 */
export default class GalleryAjaxLoader {
    constructor(masonryGallery, options = {}) {
        this.gallery = masonryGallery;
        this.button = document.querySelector('[data-gallery-load-more]');
        this.container = document.querySelector('[data-gallery-container]');
        this.hasMore = this.container ? this.container.dataset.hasMore === '1' : false;

        // Konfiguracja
        this.config = {
            action: options.action,
            nonce: options.nonce || '',
            ajaxUrl: options.ajaxUrl,
            ...options
        };

        this.state = {
            page: 1,
            loading: false,
            hasMore: this.hasMore
        };

        this.init();
    }

    /**
   * Inicjalizacja
   */
    init() {
        if (this.hasMore) {
            this.button.addEventListener('click', (e) => {
                e.preventDefault();
                this.loadMore();
            });
        }
    }

    /**
   * Ładowanie kolejnych elementów
   */
    async loadMore() {
        if (this.state.loading || !this.state.hasMore) 
            return;
        
        this.state.loading = true;
        this.setButtonLoading(true);

        try {
            const response = await this.fetchItems();

            if (response.success && response.data.html) {
                // Dodajemy nowe elementy do galerii
                this.gallery.addItems(response.data.html);

                // Aktualizujemy stan
                this.state.page++;
                this.state.hasMore = response.data.has_more;

                // Jeśli nie ma więcej elementów, ukrywamy przycisk
                if (!this.state.hasMore) {
                    this.hideButton();
                }
            } else {
                this.state.hasMore = false;
                this.hideButton();
            }
        } catch (error) {
            this.showError();
        } finally {
            setTimeout(() => {
                this.state.loading = false;
                this.setButtonLoading(false);
            }, 500);
        }
    }

    /**
   * Pobieranie elementów z serwera
   * @returns {Promise}
   */
    async fetchItems() {
        const formData = new FormData();
        formData.append('action', this.config.action);
        formData.append('page', this.state.page + 1);
        formData.append('per_page', this.config.perPage);
        formData.append('nonce', this.config.nonce);

        const response = await fetch(this.config.ajaxUrl, {
            method: 'POST',
            body: formData
        });

        return response.json();
    }

    /**
   * Ustawienie stanu ładowania przycisku
   * @param {boolean} loading
   */
    setButtonLoading(loading) {
        if (loading) {
            this.button.disabled = true;
            this.button.classList.add('loading');
            this.originalText = this.button.innerHTML;
            this.button.innerHTML = '<span class="loader">&nbsp;</span>';
        } else {
            this.button.disabled = false;
            this.button.classList.remove('loading');
            if (this.originalText) {
                this.button.innerHTML = this.originalText;
            }
        }
    }

    /**
   * Ukrycie przycisku
   */
    hideButton() {
        if (this.button) {
            this.button.style.display = 'none';
        }
    }

    /**
   * Pokazanie błędu
   */
    showError() {
        const errorEl = document.createElement('div');
        errorEl.className = 'gallery-error text-red-500 text-center mt-4';
        errorEl.textContent = 'Wystąpił błąd podczas ładowania. Spróbuj ponownie później.';
        this
            .button
            .parentNode
            .insertBefore(errorEl, this.button);
        this.hideButton();
    }
}
