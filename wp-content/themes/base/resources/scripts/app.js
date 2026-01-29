import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import navToggler from './components/nav-toggler.js';

navToggler();
/**
 * Initialize Lucide Icons
 * @see https://lucide.dev/guide/packages/lucide
 */
import {createIcons, icons} from 'lucide';

// Initialize all Lucide icons on page load
document.addEventListener('DOMContentLoaded', () => {
    createIcons({icons});
    initializeQuillEditors();
});

// Reinitialize icons after dynamic content load (for AJAX/blocks)
export function reinitLucideIcons() {
    createIcons({icons});
}

// Make it globally available
window.reinitLucideIcons = reinitLucideIcons;