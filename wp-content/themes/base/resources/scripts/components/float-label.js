export default function floatLabel(selector) {
    document.querySelectorAll(selector).forEach(function(el){
        let input = el.getElementsByTagName('input')[0];
        let label = el.getElementsByTagName('label')[0];

        if (!input) {
            input = el.getElementsByTagName('textarea')[0];
        }
        if (!input) {
            input = el.getElementsByTagName('select')[0];
        }
        if (!input) {
            return;
        }

        if (label) {
            if (input.value !== ''){
                label.classList.add('active');
            }
            else {
                label.classList.remove('active');
            }

            input.addEventListener('focus', function(){
                if (input.value === ''){
                    label.classList.add('active');
                }
                label.classList.add('focus');
            });
            input.addEventListener('blur', function(){
                if (input.value === ''){
                    label.classList.remove('active'); 
                }
                label.classList.remove('focus');
            });
        }
    });
}