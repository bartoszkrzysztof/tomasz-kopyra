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
        slidesPerView: 'auto',
        freeMode: true,
        watchSlidesProgress: true,
    });

    // Initialize main slider
    const mainSlider = new Swiper('.single-slider-main', {
        modules: [Navigation, Thumbs],
        navigation: {
            nextEl: '.single-slider-next',
            prevEl: '.single-slider-prev',
        },
        thumbs: {
            swiper: thumbsSlider,
        },
    });
});
