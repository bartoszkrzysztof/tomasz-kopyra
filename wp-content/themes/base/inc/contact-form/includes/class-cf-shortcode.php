<?php
/**
 * Shortcode dla formularzy kontaktowych
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Shortcode {

    public function __construct()
    {
        add_shortcode('contact_form', [$this, 'render_form']);
    }

    /**
     * Renderuje formularz kontaktowy
     * 
     * @param array $atts Atrybuty shortcode
     * @return string HTML formularza
     */
    public function render_form($atts)
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts);

        $form_id = intval($atts['id']);

        if ($form_id <= 0) {
            return '<p>Błąd: nieprawidłowe ID formularza.</p>';
        }

        // Sprawdzenie czy formularz istnieje
        $form = get_post($form_id);
        if (!$form || $form->post_type !== 'cf-form' || $form->post_status !== 'publish') {
            return '<p>Błąd: formularz nie istnieje lub nie jest opublikowany.</p>';
        }

        // Ładowanie skryptów i styli
        wp_enqueue_script('cf-validation');
        wp_enqueue_style('cf-form-style');

        // Pobranie danych formularza za pomocą Field Manager
        $field_manager = new CF_Field_Manager();
        $fields = $field_manager->get_fields($form_id);

        // Sprawdzenie czy istnieje własny widok HTML
        $form_view = get_post_meta($form_id, '_cf_form_view', true);

        // Pobranie dodatkowych opcji formularza
        $wrapper_classes = get_post_meta($form_id, '_cf_form_wrapper_classes', true);
        $form_classes = get_post_meta($form_id, '_cf_form_classes', true);
        $submit_classes = get_post_meta($form_id, '_cf_submit_classes', true);
        $submit_text = get_post_meta($form_id, '_cf_submit_text', true);
        
        // Wartości domyślne
        if (empty($submit_text)) {
            $submit_text = 'Wyślij';
        }

        // Przygotowanie klas CSS
        $wrapper_class_attr = 'cf-form-wrapper';
        if (!empty($wrapper_classes)) {
            $wrapper_class_attr .= ' ' . esc_attr($wrapper_classes);
        }

        $form_class_attr = 'cf-form';
        if (!empty($form_classes)) {
            $form_class_attr .= ' ' . esc_attr($form_classes);
        }

        $submit_class_attr = 'cf-submit-button';
        if (!empty($submit_classes)) {
            $submit_class_attr .= ' ' . esc_attr($submit_classes);
        }

        // Pobranie komunikatów formularza
        $validation_error_msg = get_post_meta($form_id, '_cf_validation_error_message', true);
        if (empty($validation_error_msg)) {
            $validation_error_msg = 'Popraw błędy w formularzu';
        }

        // Rozpoczęcie bufora
        ob_start();
        ?>
        <div class="<?php echo $wrapper_class_attr; ?>" data-form-id="<?php echo esc_attr($form_id); ?>">
            <form class="<?php echo $form_class_attr; ?>" 
                  id="cf-form-<?php echo esc_attr($form_id); ?>" 
                  method="post" 
                  enctype="multipart/form-data"
                  data-validation-error-message="<?php echo esc_attr($validation_error_msg); ?>">
                <?php wp_nonce_field('cf_form_submit_' . $form_id, 'cf_form_nonce'); ?>
                <input type="hidden" name="cf_form_id" value="<?php echo esc_attr($form_id); ?>">

                <div class="cf-form-messages"></div>

                <?php
                // Jeśli istnieje własny widok HTML, użyj parsera
                if (!empty($form_view)) {
                    $parser = new CF_View_Parser();
                    echo $parser->parse_view($form_view, $fields, $form_id);
                } 
                // W przeciwnym razie renderuj pola standardowo
                elseif (!empty($fields) && is_array($fields)) {
                    foreach ($fields as $field) {
                        if (!empty($field) && is_array($field)) {
                            $this->render_field($field);
                        }
                    }
                } else {
                    // Informacja o braku pól
                    ?>
                    <div class="cf-notice">
                        <p>Pola formularza nie zostały zdefiniowane. Przejdź do panelu administracyjnego i zdefiniuj pola formularza.</p>
                    </div>
                    <?php
                }
                ?>

                <?php if (!empty($fields)): ?>
                    <?php 
                    // Sprawdź czy reCAPTCHA jest włączona dla tego formularza
                    $enable_recaptcha = get_post_meta($form_id, '_cf_enable_recaptcha', true);
                    $recaptcha_configured = CF_Settings::is_recaptcha_configured();
                    
                    if ($enable_recaptcha && $recaptcha_configured):
                        // Załaduj skrypt reCAPTCHA
                        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', [], null, true);
                        $site_key = CF_Settings::get_site_key();
                    ?>
                    <div class="cf-field-wrapper cf-recaptcha-wrapper">
                        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    </div>
                    <?php endif; ?>
                    
                <div class="cf-field-wrapper cf-submit-wrapper">
                    <button type="submit" class="<?php echo $submit_class_attr; ?>"><?php echo esc_html($submit_text); ?> <i data-lucide="arrow-right"></i></button>
                </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderuje pojedyncze pole formularza
     * 
     * @param array $field Konfiguracja pola
     */
    private function render_field($field)
    {
        if (empty($field['name']) || empty($field['type'])) {
            return;
        }

        $name = esc_attr($field['name']);
        $type = esc_attr($field['type']);
        $label = !empty($field['label']) ? esc_html($field['label']) : ucfirst($name);
        $value = !empty($field['value']) ? esc_attr($field['value']) : '';
        $placeholder = !empty($field['placeholder']) ? esc_attr($field['placeholder']) : '';
        $required = !empty($field['required']);
        $css_classes = !empty($field['css_classes']) ? $field['css_classes'] : [];
        
        // Dodanie klasy do wrappera
        $wrapper_classes = ['cf-field-wrapper', 'cf-field-' . $type];
        if (!empty($css_classes)) {
            $wrapper_classes = array_merge($wrapper_classes, $css_classes);
        }
        
        $field_id = 'cf-' . $name . '-' . uniqid();
        
        ?>
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
            <?php if ($type !== 'hidden' && $type !== 'checkbox' && $type !== 'checkboxes'): ?>
                <label for="<?php echo $field_id; ?>">
                    <?php echo $label; ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>

            <?php
            // Renderowanie pola według typu
            switch ($type) {
                case 'textarea':
                    $this->render_textarea($field, $field_id);
                    break;
                
                case 'select':
                    $this->render_select($field, $field_id);
                    break;
                
                case 'multiselect':
                    $this->render_multiselect($field, $field_id);
                    break;
                
                case 'radio':
                    $this->render_radio($field);
                    break;
                
                case 'checkbox':
                    $this->render_checkbox($field, $field_id, $label);
                    break;
                
                case 'checkboxes':
                    $this->render_checkboxes($field, $label);
                    break;
                
                case 'file':
                    $this->render_file($field, $field_id);
                    break;
                
                default:
                    // text, email, tel, url, number, date, time, hidden, itp.
                    $this->render_input($field, $field_id);
                    break;
            }
            ?>
        </div>
        <?php
    }

    /**
     * Renderuje pole input
     */
    private function render_input($field, $field_id)
    {
        $attributes = $this->get_field_attributes($field, $field_id);
        ?>
        <input <?php echo $attributes; ?>>
        <?php
    }

    /**
     * Renderuje pole textarea
     */
    private function render_textarea($field, $field_id)
    {
        $name = esc_attr($field['name']);
        $value = !empty($field['value']) ? esc_textarea($field['value']) : '';
        $placeholder = !empty($field['placeholder']) ? esc_attr($field['placeholder']) : '';
        $required = !empty($field['required']) ? 'required' : '';
        $rows = !empty($field['rows']) ? intval($field['rows']) : 5;
        
        // Atrybuty data-message-*
        $data_attrs = '';
        if (!empty($field['required'])) {
            $messages = CF_Validator::get_all_messages_for_lang();
            $label = $field['label'] ?? $field['name'];
            if (isset($messages[CF_Validator::MSG_FIELD_REQUIRED])) {
                $data_attrs .= sprintf(' data-message-required="%s"', esc_attr(sprintf($messages[CF_Validator::MSG_FIELD_REQUIRED], $label)));
            }
        }
        ?>
        <textarea id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" rows="<?php echo $rows; ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required; ?><?php echo $data_attrs; ?>><?php echo $value; ?></textarea>
        <?php
    }

    /**
     * Renderuje pole select
     */
    private function render_select($field, $field_id)
    {
        $name = esc_attr($field['name']);
        $required = !empty($field['required']) ? 'required' : '';
        $options = !empty($field['options']) ? $field['options'] : [];
        $selected_value = !empty($field['value']) ? $field['value'] : '';
        ?>
        <select id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" <?php echo $required; ?>>
            <option value="">-- Wybierz --</option>
            <?php foreach ($options as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>" <?php selected($selected_value, $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Renderuje pole radio
     */
    private function render_radio($field)
    {
        $name = esc_attr($field['name']);
        $required = !empty($field['required']) ? 'required' : '';
        $options = !empty($field['options']) ? $field['options'] : [];
        $selected_value = !empty($field['value']) ? $field['value'] : '';
        
        foreach ($options as $value => $label):
            $radio_id = 'cf-' . $name . '-' . sanitize_title($value);
        ?>
            <div class="cf-radio-option">
                <input type="radio" id="<?php echo $radio_id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr($value); ?>" <?php checked($selected_value, $value); ?> <?php echo $required; ?>>
                <label for="<?php echo $radio_id; ?>"><?php echo esc_html($label); ?></label>
            </div>
        <?php 
        endforeach;
    }

    /**
     * Renderuje pole checkbox
     */
    private function render_checkbox($field, $field_id, $label)
    {
        $name = esc_attr($field['name']);
        $value = !empty($field['value']) ? esc_attr($field['value']) : '1';
        $required = !empty($field['required']) ? 'required' : '';
        $checked = !empty($field['checked']) ? 'checked' : '';
        ?>
        <div class="cf-checkbox-option">
            <input type="checkbox" id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> <?php echo $required; ?>>
            <label for="<?php echo $field_id; ?>">
                <?php echo $label; ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
        </div>
        <?php
    }

    /**
     * Renderuje listę checkboxów (checkboxes)
     */
    private function render_checkboxes($field, $label)
    {
        $name = esc_attr($field['name']);
        $required = !empty($field['required']) ? 'required' : '';
        $options = !empty($field['options']) ? $field['options'] : [];
        $selected_values = !empty($field['value']) && is_array($field['value']) ? $field['value'] : [];
        
        ?>
        <fieldset class="cf-checkboxes-group">
            <legend>
                <?php echo esc_html($label); ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </legend>
            <?php foreach ($options as $value => $option_label): 
                $checkbox_id = 'cf-' . $name . '-' . sanitize_title($value);
                $checked = in_array($value, $selected_values) ? 'checked' : '';
            ?>
                <div class="cf-checkbox-option">
                    <input type="checkbox" 
                           id="<?php echo $checkbox_id; ?>" 
                           name="<?php echo $name; ?>[]" 
                           value="<?php echo esc_attr($value); ?>" 
                           <?php echo $checked; ?>
                           <?php echo $required; ?>>
                    <label for="<?php echo $checkbox_id; ?>"><?php echo esc_html($option_label); ?></label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    /**
     * Renderuje pole multiselect
     */
    private function render_multiselect($field, $field_id)
    {
        $name = esc_attr($field['name']);
        $required = !empty($field['required']) ? 'required' : '';
        $options = !empty($field['options']) ? $field['options'] : [];
        $selected_values = !empty($field['value']) && is_array($field['value']) ? $field['value'] : [];
        $size = !empty($field['size']) ? intval($field['size']) : 5;
        
        ?>
        <select id="<?php echo $field_id; ?>" 
                name="<?php echo $name; ?>[]" 
                multiple 
                size="<?php echo $size; ?>" 
                class="cf-multiselect" 
                <?php echo $required; ?>>
            <?php foreach ($options as $value => $option_label): 
                $selected = in_array($value, $selected_values) ? 'selected' : '';
            ?>
                <option value="<?php echo esc_attr($value); ?>" <?php echo $selected; ?>>
                    <?php echo esc_html($option_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">Przytrzymaj Ctrl (Cmd na Mac) aby wybrać wiele opcji</p>
        <?php
    }

    /**
     * Renderuje pole file
     */
    private function render_file($field, $field_id)
    {
        $name = esc_attr($field['name']);
        $required = !empty($field['required']) ? 'required' : '';
        $accept = !empty($field['allowed_types']) ? 'accept=".' . implode(', .', $field['allowed_types']) . '"' : '';
        ?>
        <input type="file" id="<?php echo $field_id; ?>" name="<?php echo $name; ?>" <?php echo $accept; ?> <?php echo $required; ?>>
        <?php
        if (!empty($field['allowed_types']) || !empty($field['max_size'])):
        ?>
            <p class="cf-field-description">
                <?php if (!empty($field['allowed_types'])): ?>
                    Dozwolone typy: <?php echo implode(', ', $field['allowed_types']); ?><br>
                <?php endif; ?>
                <?php if (!empty($field['max_size'])): ?>
                    Maksymalny rozmiar: <?php echo size_format($field['max_size']); ?>
                <?php endif; ?>
            </p>
        <?php
        endif;
    }

    /**
     * Tworzy string z atrybutami pola
     */
    private function get_field_attributes($field, $field_id)
    {
        $attributes = [
            'type' => esc_attr($field['type']),
            'id' => $field_id,
            'name' => esc_attr($field['name']),
        ];

        if (!empty($field['value'])) {
            $attributes['value'] = esc_attr($field['value']);
        }

        if (!empty($field['placeholder'])) {
            $attributes['placeholder'] = esc_attr($field['placeholder']);
        }

        if (!empty($field['required'])) {
            $attributes['required'] = 'required';
        }

        // Dodatkowe atrybuty z konfiguracji
        if (!empty($field['attributes']) && is_array($field['attributes'])) {
            foreach ($field['attributes'] as $attr => $val) {
                $attributes[$attr] = esc_attr($val);
            }
        }

        // Dodanie atrybutów data-message-* dla walidacji JS
        $this->add_validation_messages_attributes($attributes, $field);

        // Budowanie stringa
        $attr_string = '';
        foreach ($attributes as $key => $value) {
            if ($value === 'required') {
                $attr_string .= ' required';
            } else {
                $attr_string .= sprintf(' %s="%s"', $key, $value);
            }
        }

        return trim($attr_string);
    }

    /**
     * Dodaje atrybuty data-message-* dla walidacji po stronie JS
     * 
     * @param array &$attributes Referencja do tablicy atrybutów
     * @param array $field Konfiguracja pola
     */
    private function add_validation_messages_attributes(&$attributes, $field)
    {
        $type = $field['type'] ?? 'text';
        $required = !empty($field['required']);
        $label = $field['label'] ?? $field['name'];

        // Pobranie komunikatów dla aktualnego języka
        $messages = CF_Validator::get_all_messages_for_lang();

        // Komunikat dla pola wymaganego
        if ($required && isset($messages[CF_Validator::MSG_FIELD_REQUIRED])) {
            $attributes['data-message-required'] = sprintf($messages[CF_Validator::MSG_FIELD_REQUIRED], $label);
        }

        // Komunikaty według typu pola
        switch ($type) {
            case 'email':
                if (isset($messages[CF_Validator::MSG_INVALID_EMAIL])) {
                    $attributes['data-message-validate-email'] = sprintf($messages[CF_Validator::MSG_INVALID_EMAIL], $label);
                }
                break;

            case 'url':
                if (isset($messages[CF_Validator::MSG_INVALID_URL])) {
                    $attributes['data-message-validate-url'] = sprintf($messages[CF_Validator::MSG_INVALID_URL], $label);
                }
                break;

            case 'number':
                if (isset($messages[CF_Validator::MSG_INVALID_NUMBER])) {
                    $attributes['data-message-validate-number'] = sprintf($messages[CF_Validator::MSG_INVALID_NUMBER], $label);
                }
                break;

            case 'tel':
                if (isset($messages[CF_Validator::MSG_INVALID_PHONE])) {
                    $attributes['data-message-validate-phone'] = sprintf($messages[CF_Validator::MSG_INVALID_PHONE], $label);
                }
                break;
        }
    }
}
