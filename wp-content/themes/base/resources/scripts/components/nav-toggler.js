export default function navToggler() {
    const togglerClass = 'js-nav-toggler';
    const togglerActiveClass = 'active';
    const togglerTargetAttr = 'data-target';
    const nav = document.getElementById('js-main-header');

    const togglers = document.querySelectorAll(`.${togglerClass}`); 
    if (!togglers.length) {
        return;
    }

    togglers.forEach((toggler) => {
        const targetSelector = toggler.getAttribute(togglerTargetAttr);
        if (!targetSelector) {
            return;
        }
        const target = document.querySelector(targetSelector);
        if (!target) {
            return;
        }

        toggler.addEventListener('click', () => {
            target.classList.toggle(togglerActiveClass);
            toggler.classList.toggle(togglerActiveClass);

            let others = document.querySelectorAll(`[data-target="${targetSelector}"]`);
            others.forEach((other) => {
                if (other !== toggler) {
                    other.classList.toggle(togglerActiveClass);
                }
            });
        });
    });

    // Scroll hide/show animation
    let lastScrollTop = 0;
    const scrollThreshold = 50; 

    const menu = document.getElementById('js-main-nav');
    const lang_menu = document.getElementById('js-lang-nav');
    
    if (nav) {
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
                if (menu && menu.classList.contains('active') || (lang_menu && lang_menu.classList.contains('active'))) {
                    return;
                }
                nav.classList.add('nav-hidden');
            } 
            else if (scrollTop < lastScrollTop) {
                nav.classList.remove('nav-hidden');
            }
            if ( scrollTop > scrollThreshold) {
                nav.classList.add('is-scrolled');
            }
            else {
                nav.classList.remove('is-scrolled');
            }
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }, false);
    }

    function updateNavPosition() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > scrollThreshold) {
            nav.classList.add('is-scrolled');
        }
    }
    updateNavPosition();
}