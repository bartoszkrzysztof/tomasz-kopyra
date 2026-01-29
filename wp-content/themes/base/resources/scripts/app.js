import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
import navToggler from './components/nav-toggler.js';
import initializeFileUploader from './components/file-uploader-init.js';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

navToggler();
initializeFileUploader();

/**
 * Initialize Quill WYSIWYG Editor
 */
function initializeQuillEditors() {
    const quillContainers = document.querySelectorAll('[id^="quill-editor-"]');
    
    quillContainers.forEach((container) => {
        const caseId = container.id.replace('quill-editor-', '');
        
        const quill = new Quill(`#quill-editor-${caseId}`, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });
        
        // Zapisz zawartość do hidden input przed wysłaniem
        const form = container.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const contentInput = document.getElementById(`post-content-input-${caseId}`);
                if (contentInput) {
                    contentInput.value = quill.root.innerHTML;
                }
            });
        }
    });
}

/**
 * Initialize Lucide Icons
 * @see https://lucide.dev/guide/packages/lucide
 */
import {createIcons, icons} from 'lucide';

// Initialize all Lucide icons on page load
document.addEventListener('DOMContentLoaded', () => {
    createIcons({icons});
    initializeQuillEditors();
});

// Reinitialize icons after dynamic content load (for AJAX/blocks)
export function reinitLucideIcons() {
    createIcons({icons});
}

// Make it globally available
window.reinitLucideIcons = reinitLucideIcons;
window.FileUploader = FileUploader;

const formClass = 'js-add-form';
const formElement = document.querySelectorAll(`.${formClass}`);
formElement.forEach((form) => {
    form.addEventListener('submit', async (e) => {
        confirm('Czy na pewno dodać dane?');
    });
});