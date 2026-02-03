/**
 * Walidacja formularzy kontaktowych
 * 
 * @package ContactForm
 */

(function($) {
    'use strict';

    /**
     * Klasa obsługująca walidację formularza
     */
    class ContactFormValidator {
        constructor(form) {
            this.form = $(form);
            this.formId = this.form.find('input[name="cf_form_id"]').val();
            this.messagesContainer = this.form.find('.cf-form-messages');
            this.submitButton = this.form.find('.cf-submit-button');
            
            this.init();
        }

        /**
         * Inicjalizacja walidatora
         */
        init() {
            // Walidacja na żywo dla poszczególnych pól
            this.form.find('input, textarea, select').on('blur', (e) => {
                this.validateField($(e.target));
            });

            // Obsługa wysyłki formularza
            this.form.on('submit', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }

        /**
         * Waliduje pojedyncze pole
         */
        validateField($field) {
            const fieldName = $field.attr('name');
            const fieldValue = $field.val();
            const fieldType = $field.attr('type') || 'text';
            const isRequired = $field.prop('required') || $field.hasClass('required');

            let error = '';

            // Sprawdzenie czy pole jest wymagane
            if (isRequired && !fieldValue) {
                // Pobierz komunikat z atrybutu data-message-required
                error = $field.attr('data-message-required') || 'To pole jest wymagane';
            }

            // Walidacja według typu
            if (!error && fieldValue) {
                switch (fieldType) {
                    case 'email':
                        if (!this.isValidEmail(fieldValue)) {
                            // Pobierz komunikat z atrybutu data-message-validate-email
                            error = $field.attr('data-message-validate-email') || 'Wprowadź poprawny adres email';
                        }
                        break;

                    case 'url':
                        if (!this.isValidUrl(fieldValue)) {
                            // Pobierz komunikat z atrybutu data-message-validate-url
                            error = $field.attr('data-message-validate-url') || 'Wprowadź poprawny adres URL';
                        }
                        break;

                    case 'tel':
                        if (!this.isValidPhone(fieldValue)) {
                            // Pobierz komunikat z atrybutu data-message-validate-phone
                            error = $field.attr('data-message-validate-phone') || 'Wprowadź poprawny numer telefonu';
                        }
                        break;

                    case 'number':
                        if (!this.isValidNumber(fieldValue)) {
                            // Pobierz komunikat z atrybutu data-message-validate-number
                            error = $field.attr('data-message-validate-number') || 'Wprowadź poprawną liczbę';
                        }
                        break;
                }
            }

            // Hook pozwalający na dodanie własnej walidacji
            error = this.applyCustomValidation(fieldName, fieldValue, error);

            // Wyświetlenie lub usunięcie błędu
            this.showFieldError($field, error);

            return !error;
        }

        /**
         * Waliduje wszystkie pola formularza
         */
        validateAllFields() {
            let isValid = true;
            const errors = {};

            this.form.find('input, textarea, select').each((index, field) => {
                const $field = $(field);
                if (!this.validateField($field)) {
                    isValid = false;
                    const fieldName = $field.attr('name');
                    errors[fieldName] = this.getFieldError($field);
                }
            });

            return { isValid, errors };
        }

        /**
         * Obsługuje wysyłkę formularza
         */
        async handleSubmit() {
            // Wyczyść poprzednie komunikaty
            this.clearMessages();

            // Walidacja wszystkich pól
            const validation = this.validateAllFields();
            
            if (!validation.isValid) {
                // Pobierz komunikat błędu walidacji z formularza (jeśli jest dostępny)
                const validationErrorMsg = this.form.attr('data-validation-error-message') || 'Popraw błędy w formularzu';
                this.showMessage(validationErrorMsg, 'error');
                return;
            }

            // Blokada przycisku
            this.submitButton.prop('disabled', true).addClass('loading');

            try {
                // Przygotowanie danych
                const formData = new FormData(this.form[0]);
                const data = {};
                
                formData.forEach((value, key) => {
                    if (key !== 'cf_form_nonce' && key !== 'cf_form_id') {
                        data[key] = value;
                    }
                });

                // Wysłanie przez REST API
                const response = await this.submitToAPI(data);

                if (response.success) {
                    this.showMessage(response.message || 'Formularz został wysłany pomyślnie', 'success');
                    this.form[0].reset();
                    this.clearAllFieldErrors();
                } else {
                    this.showMessage(response.message || 'Wystąpił błąd podczas wysyłania formularza', 'error');
                    
                    if (response.errors) {
                        this.showFieldErrors(response.errors);
                    }
                }
            } catch (error) {
                console.error('Błąd wysyłania formularza:', error);
                this.showMessage('Wystąpił błąd podczas wysyłania formularza. Spróbuj ponownie później.', 'error');
            } finally {
                // Odblokowanie przycisku
                this.submitButton.prop('disabled', false).removeClass('loading');
            }
        }

        /**
         * Wysyła dane do REST API
         */
        async submitToAPI(data) {
            const nonce = this.form.find('input[name="cf_form_nonce"]').val();
            
            const response = await fetch(cfData.ajaxUrl.replace('/validate', '/submit'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': cfData.nonce
                },
                body: JSON.stringify({
                    form_id: this.formId,
                    form_data: data,
                    nonce: nonce
                })
            });

            return await response.json();
        }

        /**
         * Wyświetla główny komunikat
         */
        showMessage(message, type) {
            const cssClass = type === 'success' ? 'cf-message-success' : 'cf-message-error';
            this.messagesContainer.html(`<div class="cf-message ${cssClass}">${message}</div>`);
            
            // Scroll do komunikatu
            $('html, body').animate({
                scrollTop: this.messagesContainer.offset().top - 100
            }, 300);

            // Automatyczne ukrycie komunikatu sukcesu po 5 sekundach
            if (type === 'success') {
                setTimeout(() => {
                    this.messagesContainer.fadeOut();
                }, 5000);
            }
        }

        /**
         * Czyści komunikaty
         */
        clearMessages() {
            this.messagesContainer.empty().show();
        }

        /**
         * Wyświetla błąd dla pola
         */
        showFieldError($field, error) {
            const $wrapper = $field.closest('.cf-field-wrapper');
            $wrapper.find('.cf-field-error').remove();

            if (error) {
                $field.addClass('cf-field-invalid');
                $wrapper.append(`<span class="cf-field-error">${error}</span>`);
            } else {
                $field.removeClass('cf-field-invalid');
            }
        }

        /**
         * Wyświetla błędy dla wielu pól
         */
        showFieldErrors(errors) {
            for (const [fieldName, error] of Object.entries(errors)) {
                const $field = this.form.find(`[name="${fieldName}"]`);
                this.showFieldError($field, error);
            }
        }

        /**
         * Pobiera błąd dla pola
         */
        getFieldError($field) {
            const $wrapper = $field.closest('.cf-field-wrapper');
            const $error = $wrapper.find('.cf-field-error');
            return $error.length ? $error.text() : '';
        }

        /**
         * Czyści wszystkie błędy pól
         */
        clearAllFieldErrors() {
            this.form.find('.cf-field-error').remove();
            this.form.find('.cf-field-invalid').removeClass('cf-field-invalid');
        }

        /**
         * Walidacja adresu email
         */
        isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        /**
         * Walidacja URL
         */
        isValidUrl(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        }

        /**
         * Walidacja numeru telefonu
         */
        isValidPhone(phone) {
            const re = /^[0-9\s\-\+\(\)]+$/;
            return re.test(phone);
        }

        /**
         * Walidacja liczby
         */
        isValidNumber(value) {
            return !isNaN(value) && isFinite(value);
        }

        /**
         * Hook pozwalający na dodanie własnej walidacji
         * Można rozszerzyć poprzez window.cfCustomValidators
         */
        applyCustomValidation(fieldName, fieldValue, currentError) {
            if (window.cfCustomValidators && window.cfCustomValidators[fieldName]) {
                const customError = window.cfCustomValidators[fieldName](fieldValue);
                return customError || currentError;
            }
            return currentError;
        }
    }

    /**
     * Inicjalizacja walidatorów dla wszystkich formularzy
     */
    $(document).ready(function() {
        $('.cf-form').each(function() {
            new ContactFormValidator(this);
        });
    });

    // Eksport do globalnego scope dla możliwości rozszerzenia
    window.ContactFormValidator = ContactFormValidator;

})(jQuery);
