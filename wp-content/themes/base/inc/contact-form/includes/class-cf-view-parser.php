<?php
/**
 * Parser widoku HTML formularza
 * Zamienia [name_pola] na pełny HTML pola formularza
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_View_Parser {

    /**
     * Instance klasy CF_Shortcode do renderowania pól
     */
    private $shortcode;

    /**
     * Tablica zdefiniowanych pól formularza
     */
    private $fields;

    public function __construct()
    {
        $this->shortcode = new CF_Shortcode();
    }

    /**
     * Parsuje widok HTML, zastępując [name_pola] renderowanymi polami
     * 
     * @param string $html_view Widok HTML z placeholderami
     * @param array $fields Tablica pól formularza
     * @param int $form_id ID formularza
     * @return string Sparsowany HTML
     */
    public function parse_view($html_view, $fields, $form_id)
    {
        if (empty($html_view)) {
            return '';
        }

        $this->fields = $this->index_fields_by_name($fields);

        // Znajdź wszystkie [name_pola] w widoku
        preg_match_all('/\[([a-zA-Z0-9_-]+)\]/', $html_view, $matches);

        if (empty($matches[1])) {
            return $html_view;
        }

        // Zastąp każdy placeholder renderowanym polem
        foreach ($matches[1] as $field_name) {
            if (isset($this->fields[$field_name])) {
                $field_html = $this->render_single_field($this->fields[$field_name]);
                $html_view = str_replace('[' . $field_name . ']', $field_html, $html_view);
            } else {
                // Jeśli pole nie istnieje, pozostaw placeholder z ostrzeżeniem
                $warning = sprintf(
                    '<div class="cf-field-warning" style="background: #fff3cd; padding: 10px; border: 1px solid #ffc107; margin: 5px 0;">⚠️ Pole <code>%s</code> nie zostało zdefiniowane w konfiguracji pól</div>',
                    esc_html($field_name)
                );
                $html_view = str_replace('[' . $field_name . ']', $warning, $html_view);
            }
        }

        return $html_view;
    }

    /**
     * Indeksuje pola według ich nazw
     * 
     * @param array $fields
     * @return array
     */
    private function index_fields_by_name($fields)
    {
        $indexed = [];
        foreach ($fields as $field) {
            if (!empty($field['name'])) {
                $indexed[$field['name']] = $field;
            }
        }
        return $indexed;
    }

    /**
     * Renderuje pojedyncze pole formularza
     * 
     * @param array $field Konfiguracja pola
     * @return string HTML pola
     */
    private function render_single_field($field)
    {
        if (empty($field['name']) || empty($field['type'])) {
            return '';
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
            if (is_string($css_classes)) {
                $css_classes = explode(' ', $css_classes);
            }
            $wrapper_classes = array_merge($wrapper_classes, $css_classes);
        }
        
        $field_id = 'cf-' . $name . '-' . uniqid();
        
        ob_start();
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
                    $this->render_input($field, $field_id);
                    break;
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderuje pole input
     */
    private function render_input($field, $field_id)
    {
        $attributes = $this->get_field_attributes($field, $field_id);
        echo '<input ' . $attributes . '>';
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
