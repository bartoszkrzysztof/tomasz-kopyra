export class FileUploader {
	constructor(container, caseId, canEdit = false, fileCategory = 0) {
    	this.container = container;
    	this.caseId = caseId;
    	this.canEdit = canEdit;
    	this.fileCategory = fileCategory;
    	this.chunkSize = 1024 * 1024 * 2; // 2MB chunks
    	this.currentUpload = null;

		this.fileInput = null;
		this.selectFileBtn = null;
		this.selectedFileNameSpan = null;
		this.progressSection = null;
		this.progressBar = null;
		this.uploadStatus = null;
		this.filesList = null;

		this.deleteFileClass = '.delete-file';
		this.requestDeleteClass = '.request-delete-file';

    	this.uploadQueue = [];
    	this.isUploading = false;

    	this.init();
  	}

	init() {
		if (!this.container) {
			console.error('FileUploader container not found');
			return;
		}

		this.fileInput = this.container.querySelector(`.file-input-${this.caseId}`);
		this.selectFileBtn = this.container.querySelector(`.select-file-btn-${this.caseId}`);
		this.selectedFileNameSpan = this.container.querySelector(`.selected-file-name-${this.caseId}`);
		this.progressSection = this.container.querySelector(`.upload-progress-${this.caseId}`);
		this.progressBar = this.container.querySelector(`.progress-bar-${this.caseId}`);
		this.uploadStatus = this.container.querySelector(`.upload-status-${this.caseId}`);
		this.filesList = this.container.querySelector(`.files-list-${this.caseId}`);

		this.attachEventListeners();
		this.loadFiles();

		// Delegate delete file button clicks
		this.filesList.addEventListener('click', (e) => {
			if (e.target && e.target.matches(this.deleteFileClass)) {
				const fileId = e.target.getAttribute('data-file-id');
				this.deleteFile(fileId);
			} 
			else if (e.target && e.target.matches(this.requestDeleteClass)) {
				const fileId = e.target.getAttribute('data-file-id');
				this.requestDelete(fileId);
			}
		});
	}

	attachEventListeners() {
		if (!this.canEdit) return;

		if (!this.fileInput || !this.selectFileBtn) return;

		this.selectFileBtn.addEventListener('click', () => this.fileInput.click());

		this.fileInput.addEventListener('change', (e) => {
			const files = Array.from(e.target.files);
			if (files.length > 0) {
				this.selectFiles(files);
			}
		});
	}

	selectFiles(files) {
		const fileNames = files.map(f => `${f.name} (${this.formatFileSize(f.size)})`).join(', ');
		this.selectedFileNameSpan.textContent = fileNames;
		
		// Add files to queue and start processing
		this.uploadQueue.push(...files);
		this.processUploadQueue();
	}

	async processUploadQueue() {
		if (this.isUploading || this.uploadQueue.length === 0) {
			return;
		}

		this.isUploading = true;
		this.progressSection.classList.remove('hidden');

		while (this.uploadQueue.length > 0) {
			const file = this.uploadQueue.shift();
			await this.uploadFile(file);
		}

		this.isUploading = false;
		
		// Reset UI after all uploads
		setTimeout(() => {
			this.progressSection.classList.add('hidden');
			this.selectedFileNameSpan.textContent = '';
			this.fileInput.value = '';
		}, 2000);
	}

	async uploadFile(file) {
		try {
			const queueInfo = this.uploadQueue.length > 0 
				? ` (pliki: ${this.uploadQueue.length + 1})` 
				: '';
			this.updateProgress(0, `Inicjalizacja: ${file.name}${queueInfo}`);

			// Step 1: Initialize upload
			const initResponse = await fetch('/wp-json/ultrawet/v1/upload/init', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings?.nonce || '',
				},
				body: JSON.stringify({
					case_id: this.caseId,
					filename: file.name,
					file_size: file.size,
					chunk_size: this.chunkSize,
					category: this.fileCategory,
				}),
			});

			if (!initResponse.ok) {
				throw new Error('Failed to initialize upload');
			}

			const initData = await initResponse.json();
			const uploadId = initData.upload_id;

			// Step 2: Upload chunks
			const totalChunks = Math.ceil(file.size / this.chunkSize);
			
			for (let i = 0; i < totalChunks; i++) {
				const start = i * this.chunkSize;
				const end = Math.min(start + this.chunkSize, file.size);
				const chunk = file.slice(start, end);

				const formData = new FormData();
				formData.append('upload_id', uploadId);
				formData.append('chunk_index', i);
				formData.append('chunk', chunk);

				const chunkResponse = await fetch('/wp-json/ultrawet/v1/upload/chunk', {
					method: 'POST',
					headers: {
						'X-WP-Nonce': window.wpApiSettings?.nonce || '',
					},
					body: formData,
				});

				if (!chunkResponse.ok) {
					throw new Error(`Failed to upload chunk ${i + 1}`);
				}

				const progress = Math.round(((i + 1) / totalChunks) * 100);
				this.updateProgress(progress, `${file.name}: ${i + 1}/${totalChunks} części${queueInfo}`);
			}

			// Step 3: Complete upload
			this.updateProgress(100, 'Finalizacja...');

			const completeResponse = await fetch('/wp-json/ultrawet/v1/upload/complete', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings?.nonce || '',
				},
				body: JSON.stringify({
					upload_id: uploadId,
					total_chunks: totalChunks,
				}),
			});

			if (!completeResponse.ok) {
				throw new Error('Failed to complete upload');
			}

			this.updateProgress(100, `Zakończono: ${file.name}${queueInfo}`);
			this.loadFiles();

		} catch (error) {
			console.error('Upload error:', error);
			this.updateProgress(0, `Błąd (${file.name}): ${error.message}`, true);
			// Continue with next file in queue even if this one failed
		}
	}

	updateProgress(percent, message, isError = false) {
		this.progressBar.style.width = `${percent}%`;
		this.progressBar.textContent = `${percent}%`;
		
		if (isError) {
			this.progressBar.classList.remove('bg-blue-500');
			this.progressBar.classList.add('bg-red-500');
		}

		this.uploadStatus.textContent = message;
	}

	async loadFiles() {
		try {
			const response = await fetch(`/wp-json/ultrawet/v1/files/${this.caseId}?category=${this.fileCategory}&show_view=true`, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings?.nonce || '',
				},
			});

			if (!response.ok) {
				throw new Error('Failed to load files');
			}

			const files = await response.json();

			this.filesList.innerHTML = files;

		} catch (error) {
			console.error('Error loading files:', error);
			this.filesList.innerHTML = '<p class="text-sm text-red-500">Błąd ładowania plików</p>';
		}
	}

	async deleteFile(fileId) {
		if (!confirm('Czy na pewno chcesz usunąć ten plik?')) {
		return;
		}

		try {
		const response = await fetch(`/wp-json/ultrawet/v1/file/${fileId}`, {
			method: 'DELETE',
			headers: {
				'X-WP-Nonce': window.wpApiSettings?.nonce || '',
			},
		});

		if (!response.ok) {
			throw new Error('Failed to delete file');
		}

		this.loadFiles();

		} catch (error) {
			console.error('Error deleting file:', error);
			alert('Błąd podczas usuwania pliku');
		}
	}

	async requestDelete(fileId) {
		if (!confirm('Czy chcesz poprosić administratora o usunięcie tego pliku?')) {
		return;
		}

		try {
			const response = await fetch(`/wp-json/ultrawet/v1/file/${fileId}/request-delete`, {
				method: 'POST',
				headers: {
					'X-WP-Nonce': window.wpApiSettings?.nonce || '',
				},
			});

			if (!response.ok) {
				throw new Error('Failed to request file deletion');
			}

			alert('Prośba o usunięcie została wysłana');
			this.loadFiles();

		} catch (error) {
			console.error('Error requesting deletion:', error);
			alert('Błąd podczas wysyłania prośby');
		}
	}

	formatFileSize(bytes) {
		if (bytes === 0) return '0 Bytes';
		const k = 1024;
		const sizes = ['Bytes', 'KB', 'MB', 'GB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
	}

	formatDate(dateString) {
		const date = new Date(dateString);
		return date.toLocaleDateString('pl-PL') + ' ' + date.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' });
	}

	escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}
}

// Make it globally accessible
window.FileUploader = FileUploader;
