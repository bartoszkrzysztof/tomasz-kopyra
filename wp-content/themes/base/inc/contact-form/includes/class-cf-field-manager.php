<?php
/**
 * Zarządzanie polami formularzy
 * Obsługa różnych źródeł definicji pól
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Field_Manager {

    /**
     * Typ źródła pól
     * Możliwe wartości: 'acf', 'json_file', 'textarea'
     */
    private $source_type;

    /**
     * Ścieżka do pliku JSON (dla source_type = 'json_file')
     */
    private $json_file_path;

    public function __construct()
    {
        // Odczyt konfiguracji z stałych lub filtrów
        // $this->source_type = $this->get_source_type();
        $this->source_type = 'acf';
        $this->json_file_path = $this->get_json_file_path();
    }

    /**
     * Pobiera typ źródła pól
     * 
     * @return string
     */
    private function get_source_type()
    {
        // Sprawdzenie stałej
        if (defined('CF_FIELD_SOURCE')) {
            return CF_FIELD_SOURCE;
        }

        // Sprawdzenie filtra
        $source = apply_filters('cf_field_source_type', 'textarea');

        // Walidacja
        $allowed = ['acf', 'json_file', 'textarea'];
        return in_array($source, $allowed) ? $source : 'textarea';
    }

    /**
     * Pobiera ścieżkę do pliku JSON
     * 
     * @return string
     */
    private function get_json_file_path()
    {
        // Sprawdzenie stałej
        if (defined('CF_JSON_FILE_PATH')) {
            return CF_JSON_FILE_PATH;
        }

        // Sprawdzenie filtra
        return apply_filters('cf_json_file_path', '');
    }

    /**
     * Pobiera pola dla danego formularza
     * 
     * @param int $form_id ID formularza
     * @return array Tablica pól
     */
    public function get_fields($form_id)
    {
        switch ($this->source_type) {
            case 'acf':
                return $this->get_fields_from_acf($form_id);
            
            case 'json_file':
                return $this->get_fields_from_json_file($form_id);
            
            case 'textarea':
            default:
                return $this->get_fields_from_textarea($form_id);
        }
    }

    /**
     * Pobiera pola z ACF Pro (repeater)
     * 
     * @param int $form_id
     * @return array
     */
    private function get_fields_from_acf($form_id)
    {
        if (!function_exists('get_field')) {
            error_log('CF_Field_Manager: ACF Pro nie jest zainstalowane');
            return [];
        }

        $fields = get_field('cf_form_fields', $form_id);
        
        if (empty($fields) || !is_array($fields)) {
            return [];
        }

        // Normalizacja pól ACF do wspólnego formatu
        $normalized_fields = [];
        foreach ($fields as $field) {
            // Konwersja max_size z MB na bajty
            if (!empty($field['max_size'])) {
                $field['max_size'] = $field['max_size'] * 1024 * 1024;
            }
            
            // Konwersja allowed_types z string na array
            if (!empty($field['allowed_types']) && is_string($field['allowed_types'])) {
                $field['allowed_types'] = array_map('trim', explode(',', $field['allowed_types']));
            }
            
            $normalized_fields[] = $this->normalize_field($field);
        }
        
        return $normalized_fields;
    }

    /**
     * Pobiera pola z pliku JSON
     * 
     * @param int $form_id
     * @return array
     */
    private function get_fields_from_json_file($form_id)
    {
        if (empty($this->json_file_path)) {
            error_log('CF_Field_Manager: Brak ścieżki do pliku JSON');
            return [];
        }

        $file_path = trailingslashit(get_template_directory()) . $this->json_file_path;
        
        if (!file_exists($file_path)) {
            error_log('CF_Field_Manager: Plik JSON nie istnieje: ' . $file_path);
            return [];
        }

        $json_content = file_get_contents($file_path);
        $all_forms = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('CF_Field_Manager: Błąd parsowania JSON: ' . json_last_error_msg());
            return [];
        }

        // Znajdź formularz o danym ID
        $fields = $all_forms[$form_id] ?? $all_forms['default'] ?? [];

        return array_map([$this, 'normalize_field'], $fields);
    }

    /**
     * Pobiera pola z textarea (meta field)
     * 
     * @param int $form_id
     * @return array
     */
    private function get_fields_from_textarea($form_id)
    {
        $fields_json = get_post_meta($form_id, '_cf_form_fields', true);
        
        if (empty($fields_json)) {
            return [];
        }

        // Jeśli już jest tablicą (zapisana jako serialized)
        if (is_array($fields_json)) {
            return array_map([$this, 'normalize_field'], $fields_json);
        }

        // Jeśli jest stringiem JSON
        $fields = json_decode($fields_json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('CF_Field_Manager: Błąd parsowania JSON z textarea: ' . json_last_error_msg());
            return [];
        }

        return array_map([$this, 'normalize_field'], $fields);
    }

    /**
     * Normalizuje pole do wspólnego formatu
     * 
     * @param array $field Surowe dane pola
     * @return array Znormalizowane pole
     */
    public function normalize_field($field)
    {
        $defaults = [
            'name' => '',
            'type' => 'text',
            'label' => '',
            'value' => '',
            'default_value' => '',
            'placeholder' => '',
            'required' => false,
            'options' => [],
            'css_classes' => [],
            'attributes' => [],
            'validation' => [],
            'conditional_logic' => [],
        ];

        $normalized = wp_parse_args($field, $defaults);

        // Obsługa value vs default_value
        if (empty($normalized['value']) && !empty($normalized['default_value'])) {
            $normalized['value'] = $normalized['default_value'];
        }

        // Konwersja css_classes do tablicy jeśli string
        if (!empty($normalized['css_classes']) && is_string($normalized['css_classes'])) {
            $normalized['css_classes'] = array_map('trim', explode(' ', $normalized['css_classes']));
        }

        // Konwersja options do tablicy jeśli string (format: value:label,value2:label2)
        if (!empty($normalized['options']) && is_string($normalized['options'])) {
            $normalized['options'] = $this->parse_options_string($normalized['options']);
        }

        // Walidacja wymaganych pól
        if (empty($normalized['name'])) {
            error_log('CF_Field_Manager: Pole bez atrybutu name zostało pominięte');
            return null;
        }

        return $normalized;
    }

    /**
     * Parsuje string z opcjami do tablicy
     * Format: "value1:Label 1,value2:Label 2" lub "value1,value2,value3"
     * 
     * @param string $options_string
     * @return array
     */
    private function parse_options_string($options_string)
    {
        $options = [];
        $items = array_map('trim', explode(',', $options_string));

        foreach ($items as $item) {
            if (strpos($item, ':') !== false) {
                list($value, $label) = array_map('trim', explode(':', $item, 2));
                $options[$value] = $label;
            } else {
                $options[$item] = $item;
            }
        }

        return $options;
    }

    /**
     * Waliduje strukturę pola
     * 
     * @param array $field
     * @return bool|WP_Error
     */
    public function validate_field_structure($field)
    {
        // Sprawdzenie wymaganych atrybutów
        if (empty($field['name'])) {
            return new WP_Error('invalid_field', 'Pole musi mieć atrybut "name"');
        }

        // Walidacja typu pola
        $allowed_types = [
            'text', 'email', 'tel', 'url', 'number', 
            'textarea', 'select', 'radio', 'checkbox',
            'file', 'date', 'time', 'datetime-local',
            'hidden', 'password'
        ];

        if (!in_array($field['type'], $allowed_types)) {
            return new WP_Error('invalid_type', sprintf('Nieprawidłowy typ pola: %s', $field['type']));
        }

        // Walidacja options dla pól select, radio, checkbox
        if (in_array($field['type'], ['select', 'radio', 'checkbox']) && empty($field['options'])) {
            return new WP_Error('missing_options', sprintf('Pole typu %s wymaga opcji', $field['type']));
        }

        return true;
    }

    /**
     * Zapisuje pola do textarea (dla source_type = 'textarea')
     * 
     * @param int $form_id
     * @param array $fields
     * @return bool
     */
    public function save_fields_to_textarea($form_id, $fields)
    {
        if ($this->source_type !== 'textarea') {
            return false;
        }

        // Walidacja każdego pola
        foreach ($fields as $field) {
            $validation = $this->validate_field_structure($field);
            if (is_wp_error($validation)) {
                error_log('CF_Field_Manager: ' . $validation->get_error_message());
            }
        }

        // Zapisanie jako JSON string
        $json = json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        return update_post_meta($form_id, '_cf_form_fields', $json);
    }

    /**
     * Pobiera typ źródła aktualnie używany
     * 
     * @return string
     */
    public function get_current_source_type()
    {
        return $this->source_type;
    }
}
