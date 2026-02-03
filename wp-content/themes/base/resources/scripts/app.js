import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import navToggler from './components/nav-toggler.js';
import floatLabel from './components/float-label.js';
import { initScrollAnimations } from './components/animations/scroll-animations.js';

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
window.addEventListener('load', () => {
    initScrollAnimations();
});

// Reinitialize icons after dynamic content load (for AJAX/blocks)
export function reinitLucideIcons() {
    createIcons({icons});
}

// Make it globally available
window.reinitLucideIcons = reinitLucideIcons;