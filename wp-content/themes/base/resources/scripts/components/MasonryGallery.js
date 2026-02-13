import Masonry from 'masonry-layout';
import imagesLoaded from 'imagesloaded';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

// Rejestracja pluginu ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

/**
 * MasonryGallery - Klasa zarządzająca galerią typu masonry
 */
export default class MasonryGallery {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        
        if (!this.container) {
            return;
        }

        this.grid = this.container.querySelector('[data-masonry-grid]');
        this.masonry = null;
        
        this.init();
    }

    /**
     * Inicjalizacja galerii
     */
    init() {
        if (!this.grid) {
            return;
        }

        // Inicjalizacja Masonry
        this.masonry = new Masonry(this.grid, {
            itemSelector: '[data-masonry-item]',
            columnWidth: '[data-masonry-sizer]',
            percentPosition: true,
            gutter: 0,
            transitionDuration: 0, // Wyłączamy wbudowaną animację (użyjemy GSAP)
        });

        // Czekamy na załadowanie obrazów
        imagesLoaded(this.grid, () => {
            this.masonry.layout();
            this.animateItems(this.grid.querySelectorAll('[data-masonry-item]'));
        });
    }

  /**
   * Animacja fade-scale dla elementów
   * @param {NodeList|Array} items - Elementy do animacji
   */
  animateItems(items) {
        gsap.fromTo(
            items,
            {
                opacity: 0,
                scale: 0.8,
            },
            {
                opacity: 1,
                scale: 1,
                duration: 0.6,
                stagger: 0.05,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: items[0]?.parentElement || this.grid,
                    start: 'top 80%', // Animacja startuje gdy górna krawędź elementu jest na 80% wysokości viewport
                    toggleActions: 'play none none none', // play przy wejściu, nic przy wyjściu/powrocie
                    // markers: true, // Odkomentuj żeby zobaczyć markery debug
                },
            }
        );

        // Opcja 2: Tylko opacity (najprostsze, bez zakłóceń)
        // gsap.fromTo(
        //     items,
        //     { opacity: 0 },
        //     {
        //         opacity: 1,
        //         duration: 0.8,
        //         stagger: 0.05,
        //         ease: 'power2.out',
        //     }
        // );

        // Opcja 3: Scale + Rotation + Opacity (bardziej dynamiczne)
        // gsap.fromTo(
        //     items,
        //     {
        //         opacity: 0,
        //         scale: 0.9,
        //         rotation: -2,
        //     },
        //     {
        //         opacity: 1,
        //         scale: 1,
        //         rotation: 0,
        //         duration: 0.6,
        //         stagger: 0.05,
        //         ease: 'back.out(1.2)',
        //     }
        // );

        // Opcja 4: Blur + Opacity (efekt wyostrzania)
        // gsap.fromTo(
        //     items,
        //     {
        //         opacity: 0,
        //         filter: 'blur(10px)',
        //     },
        //     {
        //         opacity: 1,
        //         filter: 'blur(0px)',
        //         duration: 0.8,
        //         stagger: 0.05,
        //         ease: 'power2.out',
        //     }
        // );
  }

  /**
   * Dodanie nowych elementów do galerii
   * @param {string} html - HTML nowych elementów
   */
  addItems(html) {
        if (!this.grid) return;

        // Tworzymy tymczasowy kontener
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newItems = Array.from(temp.children);

        // Dodajemy elementy do DOM bez ustawiania opacity
        // (CSS już ma opacity: 0 w .masonry-item)
        newItems.forEach((item) => {
            this.grid.appendChild(item);
        });

        // Pobieramy obrazy z nowych elementów
        const images = [];
        newItems.forEach((item) => {
            const img = item.querySelector('img');
            if (img) {
                images.push(img);
            }
        });

        // Czekamy na załadowanie wszystkich nowych obrazów
        imagesLoaded(images, { background: true }, () => {
        
        // Zamiast appended() - pełne przeliczenie wszystkich elementów
        this.masonry.reloadItems();
        this.masonry.layout();

        // Animujemy nowe elementy
        this.animateItems(newItems);
        });
  }

    /**
     * Przeliczenie layoutu (przydatne po zmianie rozmiaru)
     */
    relayout() {
        if (this.masonry) {
            this.masonry.layout();
        }
    }

    /**
     * Zniszczenie instancji
     */
    destroy() {
        if (this.masonry) {
            this.masonry.destroy();
        }
    }
}
