import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import navToggler from './components/nav-toggler.js';
import floatLabel from './components/float-label.js';
// import { initScrollAnimations } from './components/animations/scroll-animations.js';
// import './single-slider.js';

import MainAnimations from './components/main-animations.js';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

navToggler();
floatLabel('.cf-form .cf-field-text, .cf-form .cf-field-textarea, .cf-form .cf-field-email, .cf-form .cf-field-number, .cf-form .cf-field-tel');


/**
 * Initialize Lucide Icons
 * @see https://lucide.dev/guide/packages/lucide
 */
import {createIcons, icons} from 'lucide'; 
// Initialize all Lucide icons on page load
document.addEventListener('DOMContentLoaded', () => {
    createIcons({icons});
});

// Inicjalizacja animacji GSAP po pełnym załadowaniu strony (włącznie z obrazami)
// window.addEventListener('load', () => {
//     initScrollAnimations();
// });

// Reinitialize icons after dynamic content load (for AJAX/blocks)
export function reinitLucideIcons() {
    createIcons({icons});
}

// Make it globally available
window.reinitLucideIcons = reinitLucideIcons;

// Dynamically load gallery popup script only when gallery block exists
if (document.querySelector('.gallery-block')) {
    import('./blocks/gallery-popup.js').catch(err => {
        console.error('Failed to load gallery popup:', err);
    });
}

const hasAnimationBlocks = document.querySelector('.animation-double-block') || 
                          document.querySelector('.animation-gallery-block') || 
                          document.querySelector('.animation-accordion-block');

if (hasAnimationBlocks) {
    import('./blocks/animations.js').then(() => {
        setTimeout(() => {
            if (typeof ScrollTrigger !== 'undefined') {
                ScrollTrigger.refresh();
            }
            new MainAnimations();
        }, 100);
    }).catch(err => {
        console.error('Failed to load animation blocks:', err);
    });
} else {
    if (document.readyState === 'loading') {
        window.addEventListener('load', () => {
            new MainAnimations();
        });
    } else {
        new MainAnimations();
    }
}