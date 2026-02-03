<?php
/**
 * Walidator dla formularzy kontaktowych
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Validator {

    private $errors = [];

    /**
     * Klucze komunikatów błędów
     */
    const MSG_FIELD_REQUIRED = 'field_required';
    const MSG_INVALID_EMAIL = 'invalid_email';
    const MSG_INVALID_URL = 'invalid_url';
    const MSG_INVALID_NUMBER = 'invalid_number';
    const MSG_INVALID_PHONE = 'invalid_phone';
    const MSG_FILE_TOO_LARGE = 'file_too_large';
    const MSG_FILE_TYPE_NOT_ALLOWED = 'file_type_not_allowed';
    const MSG_FILE_UPLOAD_ERROR = 'file_upload_error';
    const MSG_RECAPTCHA_MISSING = 'recaptcha_missing';
    const MSG_RECAPTCHA_VERIFICATION_ERROR = 'recaptcha_verification_error';
    const MSG_RECAPTCHA_VERIFICATION_FAILED = 'recaptcha_verification_failed';

    /**
     * Domyślne komunikaty (fallback)
     */
    private $default_messages = [
        self::MSG_FIELD_REQUIRED => 'Pole: %s jest wymagane',
        self::MSG_INVALID_EMAIL => 'Pole: %s musi zawierać poprawny adres email',
        self::MSG_INVALID_URL => 'Pole: %s musi zawierać poprawny adres URL',
        self::MSG_INVALID_NUMBER => 'Pole: %s musi zawierać liczbę',
        self::MSG_INVALID_PHONE => 'Pole: %s musi zawierać poprawny numer telefonu',
        self::MSG_FILE_TOO_LARGE => 'Plik jest za duży. Maksymalny rozmiar to %s MB',
        self::MSG_FILE_TYPE_NOT_ALLOWED => 'Niedozwolony typ pliku. Dozwolone typy: %s',
        self::MSG_FILE_UPLOAD_ERROR => 'Wystąpił błąd podczas przesyłania pliku',
        self::MSG_RECAPTCHA_MISSING => 'Proszę potwierdzić, że nie jesteś robotem',
        self::MSG_RECAPTCHA_VERIFICATION_ERROR => 'Błąd weryfikacji reCAPTCHA. Spróbuj ponownie.',
        self::MSG_RECAPTCHA_VERIFICATION_FAILED => 'Weryfikacja reCAPTCHA nie powiodła się. Spróbuj ponownie.',
    ];

    /**
     * Waliduje dane formularza
     * 
     * @param array $form_data Dane z formularza
     * @param array $fields_config Konfiguracja pól
     * @param array $files Przesłane pliki
     * @param int $form_id ID formularza
     * @return array Wynik walidacji
     */
    public function validate($form_data, $fields_config = [], $files = [], $form_id = 0)
    {
        $this->errors = [];

        // Walidacja reCAPTCHA jeśli jest włączona
        if ($form_id > 0) {
            $enable_recaptcha = get_post_meta($form_id, '_cf_enable_recaptcha', true);
            if ($enable_recaptcha && CF_Settings::is_recaptcha_configured()) {
                $this->validate_recaptcha($form_data);
            }
        }

        // Walidacja na podstawie konfiguracji pól
        if (!empty($fields_config) && is_array($fields_config)) {
            foreach ($fields_config as $field) {
                $this->validate_field($form_data, $field);
            }
        } else {
            // Domyślna walidacja dla przykładowych pól
            $this->validate_default_fields($form_data);
        }

        // Walidacja plików
        if (!empty($files)) {
            $this->validate_files($files, $fields_config);
        }

        // Hook pozwalający na dodanie własnej walidacji
        $this->errors = apply_filters('cf_custom_validation', $this->errors, $form_data, $fields_config);

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
        ];
    }

    /**
     * Waliduje pojedyncze pole
     * 
     * @param array $form_data Dane formularza
     * @param array $field Konfiguracja pola
     */
    private function validate_field($form_data, $field)
    {
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? $name;
        $type = $field['type'] ?? 'text';
        $required = $field['required'] ?? false;
        $value = $form_data[$name] ?? '';

        // Sprawdzenie czy pole jest wymagane
        if ($required && empty($value)) {
            $this->errors[$name] = $this->get_message(self::MSG_FIELD_REQUIRED, $label);
            return;
        }

        // Walidacja według typu
        switch ($type) {
            case 'email':
                if (!empty($value) && !is_email($value)) {
                    $this->errors[$name] = $this->get_message(self::MSG_INVALID_EMAIL, $label);
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$name] = $this->get_message(self::MSG_INVALID_URL, $label);
                }
                break;

            case 'number':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$name] = $this->get_message(self::MSG_INVALID_NUMBER, $label);
                }
                break;

            case 'tel':
                if (!empty($value) && !preg_match('/^[0-9\s\-\+\(\)]+$/', $value)) {
                    $this->errors[$name] = $this->get_message(self::MSG_INVALID_PHONE, $label);
                }
                break;
        }

        // Hook pozwalający na dodanie własnej walidacji dla konkretnego pola
        $field_error = apply_filters('cf_validate_field_' . $name, '', $value, $field, $form_data);
        if (!empty($field_error)) {
            $this->errors[$name] = $field_error;
        }
    }

    /**
     * Domyślna walidacja dla przykładowych pól
     * 
     * @param array $form_data Dane formularza
     */
    private function validate_default_fields($form_data)
    {
        // Walidacja pola "name"
        if (empty($form_data['name'])) {
            $this->errors['name'] = 'Pole "Imię i nazwisko" jest wymagane';
        }

        // Walidacja pola "email"
        if (empty($form_data['email'])) {
            $this->errors['email'] = 'Pole "Email" jest wymagane';
        } elseif (!is_email($form_data['email'])) {
            $this->errors['email'] = 'Pole "Email" musi zawierać poprawny adres email';
        }

        // Walidacja pola "message"
        if (empty($form_data['message'])) {
            $this->errors['message'] = 'Pole "Wiadomość" jest wymagane';
        }
    }

    /**
     * Waliduje przesłane pliki
     * 
     * @param array $files Tablica $_FILES
     * @param array $fields_config Konfiguracja pól
     */
    private function validate_files($files, $fields_config)
    {
        foreach ($files as $field_name => $file) {
            // Pominięcie jeśli plik nie został wysłany
            if (empty($file['name'])) {
                continue;
            }

            // Znajdź konfigurację pola
            $field_config = $this->get_field_config_by_name($field_name, $fields_config);
            
            if (!$field_config) {
                continue;
            }

            // Sprawdzenie rozmiaru pliku
            $max_size = $field_config['max_size'] ?? 5242880; // 5MB domyślnie
            if ($file['size'] > $max_size) {
                $this->errors[$field_name] = $this->get_message(
                    self::MSG_FILE_TOO_LARGE,
                    $max_size / 1024 / 1024
                );
                continue;
            }

            // Sprawdzenie typu pliku
            $allowed_types = $field_config['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_ext, $allowed_types)) {
                $this->errors[$field_name] = $this->get_message(
                    self::MSG_FILE_TYPE_NOT_ALLOWED,
                    implode(', ', $allowed_types)
                );
                continue;
            }

            // Sprawdzenie błędów uploadu
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->errors[$field_name] = $this->get_message(self::MSG_FILE_UPLOAD_ERROR);
            }
        }
    }

    /**
     * Waliduje Google reCAPTCHA
     * 
     * @param array $form_data Dane z formularza
     */
    private function validate_recaptcha($form_data)
    {
        $recaptcha_response = $form_data['g-recaptcha-response'] ?? '';
        
        if (empty($recaptcha_response)) {
            $this->errors['recaptcha'] = $this->get_message(self::MSG_RECAPTCHA_MISSING);
            return;
        }

        $secret_key = CF_Settings::get_secret_key();
        
        // Wywołanie API Google reCAPTCHA
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = wp_remote_post($verify_url, [
            'body' => [
                'secret' => $secret_key,
                'response' => $recaptcha_response,
                'remoteip' => $this->get_user_ip()
            ]
        ]);

        if (is_wp_error($response)) {
            $this->errors['recaptcha'] = $this->get_message(self::MSG_RECAPTCHA_VERIFICATION_ERROR);
            return;
        }

        $response_body = wp_remote_retrieve_body($response);
        $result = json_decode($response_body, true);

        if (empty($result['success'])) {
            $this->errors['recaptcha'] = $this->get_message(self::MSG_RECAPTCHA_VERIFICATION_FAILED);
        }
    }

    /**
     * Pobiera IP użytkownika
     * 
     * @return string
     */
    private function get_user_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }

    /**
     * Znajduje konfigurację pola na podstawie nazwy
     * 
     * @param string $name Nazwa pola
     * @param array $fields_config Konfiguracja pól
     * @return array|null
     */
    private function get_field_config_by_name($name, $fields_config)
    {
        if (empty($fields_config) || !is_array($fields_config)) {
            return null;
        }

        foreach ($fields_config as $field) {
            if (isset($field['name']) && $field['name'] === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Pobiera aktualny kod języka
     * 
     * @return string Kod języka (np. 'pl', 'en')
     */
    private function get_current_language()
    {
        // Polylang
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language();
            if ($lang) {
                return $lang;
            }
        }

        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        }

        // Fallback - język WordPress
        $locale = get_locale();
        return substr($locale, 0, 2);
    }

    /**
     * Pobiera przetłumaczony komunikat
     * 
     * @param string $message_key Klucz komunikatu
     * @param mixed $args Argumenty do sprintf (opcjonalnie)
     * @return string Przetłumaczony komunikat
     */
    private function get_message($message_key, $args = null)
    {
        $lang = $this->get_current_language();
        $translations = CF_Settings::get_message_translations($lang);

        // Sprawdź czy istnieje tłumaczenie dla aktualnego języka
        if (isset($translations[$message_key])) {
            $message = $translations[$message_key];
        } else {
            // Fallback do domyślnego komunikatu
            $message = $this->default_messages[$message_key] ?? '';
        }

        // Jeśli są argumenty, użyj sprintf
        if ($args !== null) {
            if (is_array($args)) {
                return sprintf($message, ...$args);
            } else {
                return sprintf($message, $args);
            }
        }

        return $message;
    }

    /**
     * Pobiera komunikat walidacyjny dla konkretnego typu
     * (używane przy renderowaniu atrybutów data-message-*)
     * 
     * @param string $message_key Klucz komunikatu
     * @param string $lang Kod języka
     * @return string
     */
    public static function get_validation_message($message_key, $lang = null)
    {
        if ($lang === null) {
            $validator = new self();
            $lang = $validator->get_current_language();
        }

        $translations = CF_Settings::get_message_translations($lang);

        // Sprawdź czy istnieje tłumaczenie
        if (isset($translations[$message_key])) {
            return $translations[$message_key];
        }

        // Fallback do domyślnych komunikatów
        $validator = new self();
        return $validator->default_messages[$message_key] ?? '';
    }

    /**
     * Zwraca wszystkie komunikaty walidacyjne dla danego języka
     * (używane przy generowaniu atrybutów data-message-*)
     * 
     * @param string $lang Kod języka
     * @return array
     */
    public static function get_all_messages_for_lang($lang = null)
    {
        if ($lang === null) {
            $validator = new self();
            $lang = $validator->get_current_language();
        }

        $translations = CF_Settings::get_message_translations($lang);
        $validator = new self();

        // Połącz tłumaczenia z domyślnymi (jako fallback)
        $messages = $validator->default_messages;
        
        foreach ($translations as $key => $value) {
            if (!empty($value)) {
                $messages[$key] = $value;
            }
        }

        return $messages;
    }
}
