/**
 * Animations Entry Point
 * Osobny bundle dla animacji GSAP + ScrollTrigger
 */

import { initScrollAnimations, refreshScrollTrigger } from './components/animations/scroll-animations.js';

// Inicjalizacja po załadowaniu DOM
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initScrollAnimations);
} else {
  // DOM już załadowany
  initScrollAnimations();
}

// Refresh ScrollTrigger po załadowaniu wszystkich zasobów
window.addEventListener('load', refreshScrollTrigger);

// Export dla potencjalnego użycia zewnętrznego
export { initScrollAnimations, refreshScrollTrigger };
