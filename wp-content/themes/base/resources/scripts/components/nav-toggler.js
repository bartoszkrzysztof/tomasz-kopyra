export default function navToggler() {
    const togglerClass = 'js-nav-toggler';
    const togglerActiveClass = 'active';
    const togglerTargetAttr = 'data-target';

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
        });
    });
}