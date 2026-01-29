import { FileUploader } from "./FileUploader";

export default function initializeFileUploader() {
    const uploaderContainers = document.querySelectorAll('.js-file-uploader-container');
    uploaderContainers.forEach((container) => {
        const caseId = container.getAttribute('data-case-id');
        const canEdit = container.getAttribute('data-can-edit') === 'true';
        const fileCategory = parseInt(container.getAttribute('data-file-cat') || '0', 10);
        new FileUploader(container, caseId, canEdit, fileCategory);
    });
}