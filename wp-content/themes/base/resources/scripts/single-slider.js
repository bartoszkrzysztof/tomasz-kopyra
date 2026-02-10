import Swiper from 'swiper';
import { Navigation, Thumbs } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/thumbs';

/**
 * Initialize Single Paint Slider with thumbnails
 */
document.addEventListener('DOMContentLoaded', () => {
    const sliderElement = document.querySelector('#single-paint-slider');
    
    if (!sliderElement) {
        return;
    }

    // Initialize thumbnails slider first
    const thumbsSlider = new Swiper('.single-slider-thumbnails', {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesProgress: true,
        breakpoints: {
            640: {
                slidesPerView: 5,
            },
            768: {
                slidesPerView: 6,
            },
            1024: {
                slidesPerView: 8,
            },
        },
    });

    // Initialize main slider
    const mainSlider = new Swiper('.single-slider-main', {
        modules: [Navigation, Thumbs],
        spaceBetween: 10,
        navigation: {
            nextEl: '.single-slider-next',
            prevEl: '.single-slider-prev',
        },
        thumbs: {
            swiper: thumbsSlider,
        },
    });
});
