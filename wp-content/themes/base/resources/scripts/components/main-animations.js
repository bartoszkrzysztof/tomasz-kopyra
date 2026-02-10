import { gsap } from 'gsap';

export default class MainAnimations {
    constructor() {
        this.init();
    }

    init() {
        const headline = document.querySelector('.animate-page-headline');
        if (headline) {
            gsap.fromTo(
                headline,
                {
                    opacity: 0,
                    y: 30,
                },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    ease: 'power2.out',
                }
            );
        }

        const title = document.querySelector('.animate-page-title');
        if (title) {
            gsap.fromTo(
                title,
                {
                    opacity: 0,
                    y: 30,
                },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    ease: 'power2.out',
                }
            );
        }
    }
}