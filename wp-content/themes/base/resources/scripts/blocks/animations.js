import gsap from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';
import MasonryGallery from './../components/MasonryGallery';

gsap.registerPlugin(ScrollTrigger);

ScrollTrigger.config({
    autoRefreshEvents: "visibilitychange,DOMContentLoaded,load",
    ignoreMobileResize: true
});

// Debug mode dla developmentu
if (import.meta.env.DEV) {
    ScrollTrigger.defaults({
        markers: false // Ustaw na true aby zobaczyć markery debugowania
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initAllSections();
    });
} else {
    initAllSections();
}

function initAllSections(){
    if (document.querySelector('.animation-double-block')) {
        initSectionDouble();
    }

    if (document.querySelector('.animation-gallery-block')) {
        initSectionGallery();
    }

    if (document.querySelector('.animation-accordion-block')) {
        initSectionAccordion();
    }
}

function initSectionDouble() {
    const sections = document.querySelectorAll('.js-gsap-double-block');
    if (!sections.length) 
        return;

    sections.forEach(section => {
        const leftBox = section.querySelector('.js-left-box');
        const rightBox = section.querySelector('.js-right-box');
        const pinnedSection = section.querySelector('.js-pinned-section') || section;

        //settings from data attributes
        const scrollLength = section.dataset.scrollLength || 2000;
        const sceneOrder = section.dataset.sceneOrder || 'default';
        
        // Tworzenie osi czasu (Timeline)
        // Timeline pozwala nam układać animacje jedna po drugiej
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: pinnedSection,     // Element wyzwalający
                start: "top top",           // Start: góra sekcji dotyka góry ekranu
                end: `+=${scrollLength}`,   // Długość "scrollowania" (2000px wirtualnego scrolla)
                pin: true,                  // Przyklejenie sekcji
                scrub: 1,                   // Płynność animacji (związana z paskiem scrolla)
                anticipatePin: 1            // Zapobiega lekkim skokom przy pinowaniu
            }
        });

        let elemStart = leftBox
        let elemEnd = rightBox

        if (sceneOrder === 'reverse') {
            elemStart = rightBox;
            elemEnd = leftBox;
        }

        let startAnimationTime = elemStart.dataset.animationTime || 'full';
        let endAnimationTime = elemEnd.dataset.animationTime || 'full';
        let startAnimationType = elemStart.dataset.animationType || 'fade';
        let endAnimationType = elemEnd.dataset.animationType || 'fade';

        let argsStart = animateBox(startAnimationType);
        argsStart.duration = animationTime(startAnimationTime);
        argsStart.ease = "power1.out";

        let argsEnd = animateBox(endAnimationType);
        argsEnd.duration = animationTime(endAnimationTime);
        argsEnd.ease = "power2.out";

        tl.to(elemStart, argsStart);
        tl.to(elemEnd, argsEnd);

        const bg = section.querySelector('.js-animation-bg');   
        if (bg) {
            ScrollTrigger.create({
                trigger: section,
                start: "top top",
                end: "bottom bottom",
                pin: bg,
                pinSpacing: false
            });
        }
    });
}

function animateBox(animationType) {
    let args = {};

    switch(animationType) {
        case 'blur':
            args.filter = "blur(0px)";
            args.opacity = 1;
            break;
        case 'opacity':
            args.opacity = 1;
            break;
        case 'scale':
            args.scale = 1;
            args.opacity = 1;
            break;
        case 'fade_left':
            args.x = "0%";
            args.opacity = 1;
            break;
        case 'fade_right':
            args.x = "0%";
            args.opacity = 1;
            break;
        case 'fade_bottom':
            args.y = "0%";
            args.opacity = 1;
            break;
        case 'fade_up':
            args.y = "0%";
            args.opacity = 1;
            break;
        default:
            args.opacity = 1;
    }
    
    return args;
}
function animationTime(animationTime = 'full') {
    let time = 1;
    switch(animationTime) {
        case '3/4':
            time = 0.75;
            break;
        case '1/2':
            time = 0.5;
            break;
        default:
            time = 1;
    }
    
    return time;
}

function initSectionGallery() {
    const sectionsMasonary = document.querySelectorAll('.js-masonary-gallery-block');
    if (sectionsMasonary.length > 0) {
        sectionsMasonary.forEach(section => {
            const masonryGallery = new MasonryGallery('[data-gallery-container]');
        });
    }
    
    const sectionsGSAP = document.querySelectorAll('.js-gsap-gallery-block');
        if (sectionsGSAP.length > 0) {
        sectionsGSAP.forEach(section => {
            const bg = section.querySelector('.js-animation-bg');

            if (bg) {
                ScrollTrigger.create({
                    trigger: section,
                    start: "top top",
                    end: "bottom bottom",
                    pin: bg,
                    pinSpacing: false
                });
            }
        });
    }
}

function initSectionAccordion() {
    const sections = document.querySelectorAll('.animation-accordion-block');
    if (!sections.length) return;

    sections.forEach(section => {
        const items = section.querySelectorAll('.js-accordion-item');
        const wrapper = section.querySelector('.js-accordion-wrapper');
        if (!items.length) return;

        const totalItems = items.length;
        const expandedSize = 50; // 50% dla rozwiniętego
        const collapsedSize = (100 - expandedSize) / (totalItems - 1); // reszta proporcjonalnie

        // Animacja wejścia elementów z opóźnieniem (fade_up)
        gsap.set(items, { 
            opacity: 0, 
            y: 50 
        });

        ScrollTrigger.create({
            trigger: wrapper,
            start: "top 80%",
            onEnter: () => {
                gsap.to(items, {
                    opacity: 1,
                    y: 0,
                    duration: 0.8,
                    stagger: 0.1,
                    ease: "power2.out"
                });
            },
            once: true
        });

        // Inicjalizacja - wszystkie równe
        items.forEach(item => {
            const content = item.querySelector('.js-accordion-content');
            const contentInner = item.querySelector('.accordion-content-inner');
            gsap.set(content, { 
                opacity: 0,
                pointerEvents: 'none'
            });
            gsap.set(contentInner, {
                x: 50,
                opacity: 0
            });
        });

        // Obsługa kliknięć
        items.forEach((item, index) => {
            const trigger = item.querySelector('.js-accordion-trigger');
            const content = item.querySelector('.js-accordion-content');
            
            if (!trigger || !content) return;

            trigger.addEventListener('click', () => {
                items.forEach(otherItem => {
                    const otherContent = otherItem.querySelector('.js-accordion-content');
                    const otherContentInner = otherItem.querySelector('.accordion-content-inner');
                    
                    if (otherItem === item) {
                        otherItem.classList.add('is-active');
                        
                        gsap.set(otherContent, { pointerEvents: 'auto' });
                        gsap.to(otherContent, {
                            opacity: 1,
                            duration: 0.2,
                            ease: "power2.out"
                        });

                        gsap.to(otherItem, {
                            flexGrow: 0,
                            flexBasis: `${expandedSize}%`,
                            duration: 0.2,
                            ease: "power2.inOut",
                            onComplete: () => {
                                // Tekst pojawia się dopiero po pełnym rozwinięciu panelu
                                gsap.to(otherContentInner, {
                                    x: 0,
                                    opacity: 1,
                                    duration: 0.4,
                                    ease: "power2.out",
                                    delay: 0.5
                                });
                            }
                        });
                    } 
                    else {
                        otherItem.classList.remove('is-active');
                        
                        gsap.to(otherContentInner, {
                            x: 50,
                            opacity: 0,
                            duration: 0.2,
                            ease: "power2.in"
                        });

                        gsap.to(otherItem, {
                            flexGrow: 0,
                            flexBasis: `${collapsedSize}%`,
                            duration: 0.2,
                            ease: "power2.inOut"
                        });

                        gsap.to(otherContent, {
                            opacity: 0,
                            duration: 0.2,
                            ease: "power2.inOut",
                            onComplete: () => {
                                gsap.set(otherContent, { pointerEvents: 'none' });
                            }
                        });
                    }
                });
            });
        });
    });
}