import gsap from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';

// Rejestracja pluginu ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// Konfiguracja ScrollTrigger
ScrollTrigger.config({
    // Zapobiega problemom z pinowaniem na urządzeniach mobilnych
    autoRefreshEvents: "visibilitychange,DOMContentLoaded,load",
    // Ignorowanie transformacji dla lepszej wydajności
    ignoreMobileResize: true
});

// Debug mode dla developmentu
if (import.meta.env.DEV) {
    ScrollTrigger.defaults({
        markers: false // Ustaw na true aby zobaczyć markery debugowania
    });
}

/**
 * Inicjalizacja wszystkich animacji scroll
 * Sprawdza czy elementy istnieją przed inicjalizacją
 */
export function initScrollAnimations() {
    // Czekamy na pełne załadowanie DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initAllSections();
        });
    } else {
        initAllSections();
    }
}

/**
 * Inicjalizacja wszystkich sekcji
 */
function initAllSections() {
    // Sekcja 1: Animacja tła + wsunięcie tytułu z lewej
    initSection1();

    // Sekcja 2: Pinned background + scroll masonry gallery
    initSection2();

    // Sekcja 3: Blur-to-sharp (left) + slide-in (right)
    initSection3();

    // Sekcja 4: 4 boxy slide-up + sekwencyjne highlight
    initSection4();

    // Sekcja 5: Opacity change (right) + slide-in (left)
    initSection5();

    // Sekcja 6: Pinned background + opacity + ruch od dołu
    initSection6();
    
    // Odświeżenie ScrollTrigger po inicjalizacji
    ScrollTrigger.refresh();
}

/**
 * Sekcja 1: Animacja tła + wsunięcie tytułu z lewej
 */
function initSection1() {
    const section = document.querySelector('[data-animation="section-1"]');
    if (!section) 
        return;
    
    const bg = section.querySelector('.section-bg');

    // Timeline dla sekcji
    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: section,
            start: 'center center',
            end: '+=200vh',
            scrub: 2
        }
    });

    // Animacja opacity tła
    tl.to(bg, {
        opacity: 0,
        duration: 1
    });
}

/**
 * Sekcja 2: Pinned background + scroll masonry gallery
 */
function initSection2() {
    const section = document.querySelector('[data-animation="section-2"]');
    if (!section) 
        return;

    const wrapper = section.querySelector('.section2-wrapper') || section;
    const bg = section.querySelector('.section-bg');

    if (bg) {
        ScrollTrigger.create({
            trigger: wrapper,
            start: "top top",
            end: "bottom bottom",
            pin: bg,
            pinSpacing: false
        });
    }

    // ✅ Scoped selector - tylko elementy wewnątrz tej sekcji
    section.querySelectorAll(".gallery-item").forEach(item => {
        gsap.from(item, {
            y: 100,
            opacity: 0,
            duration: 1,
            ease: "power3.out",
            scrollTrigger: {
                trigger: item,
                start: "top 80%",
                toggleActions: "play none none none"
            }
        });
    });
}

/**
 * Sekcja 3: Blur-to-sharp (left) + slide-in (right)
 */
function initSection3() {
    const section = document.querySelector('[data-animation="section-3"]');
    if (!section) 
        return;

    const leftBox = section.querySelector('.left-box');
    const rightBox = section.querySelector('.right-box');
    const pinnedSection = section.querySelector('.pinned-section') || section;
    
    // Tworzenie osi czasu (Timeline)
    // Timeline pozwala nam układać animacje jedna po drugiej
    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: pinnedSection, // Element wyzwalający
            start: "top top",           // Start: góra sekcji dotyka góry ekranu
            end: "+=1600",              // Długość "scrollowania" (2000px wirtualnego scrolla)
            pin: true,                  // Przyklejenie sekcji
            scrub: 1,                   // Płynność animacji (związana z paskiem scrolla)
            anticipatePin: 1            // Zapobiega lekkim skokom przy pinowaniu
        }
    });

    // KROK 1: Lewa strona traci blur
    // Animujemy od obecnego stanu (CSS) do blur(0px)
    tl.to(leftBox, {
        filter: "blur(0px)",
        duration: 1, // Czas trwania jest relatywny w scrub
        ease: "power1.out"
    });

    // KROK 2: Prawa strona wsuwa się i pojawia
    // Wykona się DOPIERO po zakończeniu kroku 1
    tl.to(rightBox, {
        x: "0%",       // Powrót z translateX(100%) na 0
        opacity: 1,    // Pojawienie się
        duration: 0.5,
        ease: "power2.out"
    });
}

/**
 * Sekcja 4: 4 boxy slide-up + sekwencyjne highlight
 */
function initSection4() {
    const section = document.querySelector('[data-animation="section-4"]');
    if (!section) 
        return;
    
    const wrapper = section.querySelector('.section4-wrapper') || section;
    const bg = section.querySelector('.section-bg');

    if (bg) {
        ScrollTrigger.create({
            trigger: wrapper,
            start: "top top",
            end: "bottom bottom",
            pin: bg,
            pinSpacing: false
        });
    }
    
    // ✅ Scoped selector - tylko elementy wewnątrz tej sekcji
    section.querySelectorAll(".box-item").forEach((item, index) => {
        gsap.from(item, {
            y: 100,
            opacity: 0,
            duration: 1,
            delay: index * 0.3,
            ease: "power3.out",
            scrollTrigger: {
                trigger: item,
                start: "top 95%",
                toggleActions: "play none none none"
            }
        });
    });
}

/**
 * Sekcja 5: Opacity change (right) + slide-in (left)
 */
function initSection5() {

    const section5 = document.querySelector('[data-animation="section-5"]');
    if (!section5) 
        return;

    const leftBox5 = section5.querySelector('.left-box');
    const rightBox5 = section5.querySelector('.right-box');
    const pinWrapper5 = section5.querySelector('.pinned-section') || section5;
    
    // Tworzenie osi czasu (Timeline)
    // Timeline pozwala nam układać animacje jedna po drugiej
    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: pinWrapper5, // Element wyzwalający
            start: "top top",           // Start: góra sekcji dotyka góry ekranu
            end: "+=1600",              // Długość "scrollowania" (2000px wirtualnego scrolla)
            pin: true,                  // Przyklejenie sekcji
            scrub: 1,                   // Płynność animacji (związana z paskiem scrolla)
            anticipatePin: 1            // Zapobiega lekkim skokom przy pinowaniu
        }
    });

    // KROK 1: Lewa strona traci blur
    // Animujemy od obecnego stanu (CSS) do blur(0px)
    tl.to(rightBox5  , {
        filter: "blur(0px)",
        opacity: 1,
        duration: 1, // Czas trwania jest relatywny w scrub
        ease: "power1.out"
    });

    // KROK 2: Prawa strona wsuwa się i pojawia
    // Wykona się DOPIERO po zakończeniu kroku 1
    tl.to(leftBox5, {
        x: "0%",       // Powrót z translateX(100%) na 0
        y: "0%",       // Powrót z translateX(100%) na 0
        opacity: 1,    // Pojawienie się
        duration: 0.5,
        ease: "power2.out"
    });
}

/**
 * Sekcja 6: Pinned background + opacity + ruch od dołu
 */
function initSection6() {
    const section6 = document.querySelector('[data-animation="section-6"]');
    if (!section6) 
        return;
    
    const content = section6.querySelector('.section-content');
    const leftBox6 = section6.querySelector('.left-box');
    const rightBox6 = section6.querySelector('.right-box');
    const pinWrapper6 = section6.querySelector('.pinned-section') || section6;

    // Pin tła
    ScrollTrigger.create(
        {trigger: section6, start: 'top top', end: '+=100%', pin: true, anticipatePin: 1}
    );

    gsap.from(rightBox6, {
        y: 20,
        opacity: 0,
        duration: 1,
        delay: 0.6, // Opóźnienie na podstawie indeksu
        ease: "power3.out",
        scrollTrigger: {
            trigger: rightBox6,
            start: "top 35%", // Odpala się, gdy tekst niemal wejdzie od dołu
            toggleActions: "play false play false" 
        }
    });
    gsap.from(leftBox6, {
        y: 20,
        opacity: 0,
        duration: 1,
        ease: "power3.out",
        scrollTrigger: {
            trigger: leftBox6,
            start: "top 45%", // Odpala się, gdy tekst niemal wejdzie od dołu
            toggleActions: "play false play false" 
        }
    });
}

/**
 * Odświeżenie ScrollTrigger po załadowaniu wszystkich zasobów
 * Ważne dla poprawnych obliczeń pozycji elementów z obrazami
 */
export function refreshScrollTrigger() {
    ScrollTrigger.refresh();
}