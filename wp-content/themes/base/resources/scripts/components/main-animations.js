import { gsap } from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default class MainAnimations {
    constructor() {
        this.init();
        this.setupRefresh();
    }

    setupRefresh() {
        window.addEventListener('load', () => {
            setTimeout(() => {
                ScrollTrigger.refresh(true);
            }, 200);
        });
    }

    init() {
        const headline = document.querySelectorAll('.animate-page-headline');

        if (headline.length > 0) {
            headline.forEach((element) => {
                ScrollTrigger.create({
                    trigger: element,
                    start: "top 50%",
                    onEnter: () => {
                        gsap.fromTo(
                            element,
                            {
                                opacity: 0,
                                y: 100,
                            },
                            {
                                opacity: 1,
                                y: 0,
                                duration: 0.6,
                                ease: 'power1.out',
                            }
                        );
                    },
                    once: true
                });
            });
        }
    }
}