<?php
/**
 * Custom Post Types dla formularzy kontaktowych
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Post_Types {

    public function __construct()
    {
        add_action('init', [$this, 'register_post_types']);
    }

    /**
     * Rejestruje Custom Post Types
     */
    public function register_post_types()
    {
        $this->register_form_post_type();
        $this->register_sended_post_type();
    }

    /**
     * Rejestruje CPT dla formularzy (cf-form)
     */
    private function register_form_post_type()
    {
        $labels = [
            'name' => 'Formularze',
            'singular_name' => 'Formularz',
            'menu_name' => 'Formularze kontaktowe',
            'add_new' => 'Dodaj nowy',
            'add_new_item' => 'Dodaj nowy formularz',
            'edit_item' => 'Edytuj formularz',
            'new_item' => 'Nowy formularz',
            'view_item' => 'Zobacz formularz',
            'search_items' => 'Szukaj formularzy',
            'not_found' => 'Nie znaleziono formularzy',
            'not_found_in_trash' => 'Brak formularzy w koszu',
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_icon' => 'dashicons-email',
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        ];

        register_post_type('cf-form', $args);

        // Metaboxy dla formularza
        add_action('add_meta_boxes', [$this, 'add_form_meta_boxes']);
        add_action('save_post_cf-form', [$this, 'save_form_meta_boxes']);
    }

    /**
     * Rejestruje CPT dla wysłanych formularzy (cf-sended)
     */
    private function register_sended_post_type()
    {
        $labels = [
            'name' => 'Wysłane formularze',
            'singular_name' => 'Wysłany formularz',
            'menu_name' => 'Wysłane formularze',
            'view_item' => 'Zobacz formularz',
            'search_items' => 'Szukaj formularzy',
            'not_found' => 'Nie znaleziono formularzy',
            'not_found_in_trash' => 'Brak formularzy w koszu',
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => false,
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow',
            ],
            'map_meta_cap' => true,
            'hierarchical' => false,
            'menu_icon' => 'dashicons-email-alt',
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        ];

        register_post_type('cf-sended', $args);

        // Metaboxy dla wysłanych formularzy
        add_action('add_meta_boxes', [$this, 'add_sended_meta_boxes']);
    }

    /**
     * Dodaje metaboxy do formularza
     */
    public function add_form_meta_boxes()
    {
        add_meta_box(
            'cf_form_settings',
            'Ustawienia formularza',
            [$this, 'render_form_settings_meta_box'],
            'cf-form',
            'normal',
            'high'
        );

        add_meta_box(
            'cf_form_fields',
            'Pola formularza',
            [$this, 'render_form_fields_meta_box'],
            'cf-form',
            'normal',
            'high'
        );

        add_meta_box(
            'cf_form_view',
            'Widok HTML formularza',
            [$this, 'render_form_view_meta_box'],
            'cf-form',
            'normal',
            'default'
        );

        add_meta_box(
            'cf_email_template',
            'Szablon wiadomości email',
            [$this, 'render_email_template_meta_box'],
            'cf-form',
            'normal',
            'default'
        );

        add_meta_box(
            'cf_shortcode_info',
            'Shortcode',
            [$this, 'render_shortcode_info_meta_box'],
            'cf-form',
            'side',
            'high'
        );
    }

    /**
     * Dodaje metaboxy do wysłanych formularzy
     */
    public function add_sended_meta_boxes()
    {
        add_meta_box(
            'cf_sended_data',
            'Dane z formularza',
            [$this, 'render_sended_data_meta_box'],
            'cf-sended',
            'normal',
            'high'
        );

        add_meta_box(
            'cf_sended_meta',
            'Informacje dodatkowe',
            [$this, 'render_sended_meta_meta_box'],
            'cf-sended',
            'side',
            'default'
        );
    }

    /**
     * Renderuje metabox z polami formularza
     */
    public function render_form_fields_meta_box($post)
    {
        wp_nonce_field('cf_form_meta_box', 'cf_form_meta_box_nonce');
        
        $field_manager = new CF_Field_Manager();
        $source_type = $field_manager->get_current_source_type();
        
        ?>
        <div id="cf-form-fields-wrapper">
            <div class="cf-source-info" style="background: #f0f0f1; padding: 10px; margin-bottom: 15px; border-left: 4px solid #2271b1;">
                <strong>Źródło pól:</strong> 
                <?php 
                $source_names = [
                    'acf' => 'ACF Pro (Repeater)',
                    'json_file' => 'Plik JSON',
                    'textarea' => 'Pole tekstowe (JSON)'
                ];
                echo esc_html($source_names[$source_type] ?? $source_type);
                ?>
                <p class="description" style="margin: 5px 0 0 0;">
                    <?php if ($source_type === 'acf'): ?>
                        Pola są zarządzane przez ACF Pro. Edytuj pola w sekcji ACF poniżej.
                    <?php elseif ($source_type === 'json_file'): ?>
                        Pola są wczytywane z pliku JSON. Ścieżka: <code><?php echo esc_html(defined('CF_JSON_FILE_PATH') ? CF_JSON_FILE_PATH : 'Nie ustawiona'); ?></code>
                    <?php else: ?>
                        Definiuj pola jako JSON poniżej.
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($source_type === 'textarea'): ?>
                <div class="cf-field-editor">
                    <p><strong>Definiowanie pól (format JSON):</strong></p>
                    <p class="description">
                        Każde pole powinno zawierać: <code>name</code>, <code>type</code>, <code>label</code>, opcjonalnie: <code>value</code>, <code>options</code>, <code>css_classes</code>, <code>required</code>
                        <br><a href="#" onclick="jQuery('#cf-field-example').toggle(); return false;">Pokaż przykład</a>
                    </p>
                    
                    <div id="cf-field-example" style="display: none; background: #fafafa; padding: 10px; margin: 10px 0; border: 1px solid #ddd;">
                        <pre style="margin: 0; font-size: 12px;">[
  {
    "name": "fullname",
    "type": "text",
    "label": "Imię i nazwisko",
    "placeholder": "Jan Kowalski",
    "required": true,
    "css_classes": ["form-control", "large"]
  },
  {
    "name": "email",
    "type": "email",
    "label": "Adres email",
    "required": true
  },
  {
    "name": "phone",
    "type": "tel",
    "label": "Telefon",
    "css_classes": ["phone-input"]
  },
  {
    "name": "subject",
    "type": "select",
    "label": "Temat",
    "options": {
      "general": "Pytanie ogólne",
      "support": "Wsparcie techniczne",
      "sales": "Zapytanie handlowe"
    },
    "required": true
  },
  {
    "name": "message",
    "type": "textarea",
    "label": "Wiadomość",
    "required": true,
    "css_classes": ["large-textarea"]
  },
  {
    "name": "newsletter",
    "type": "checkbox",
    "label": "Zapisz się do newslettera",
    "value": "yes"
  }
]</pre>
                    </div>

                    <?php
                    $fields = get_post_meta($post->ID, '_cf_form_fields', true);
                    
                    // Jeśli puste, użyj przykładowego szablonu
                    if (empty($fields)) {
                        $fields = json_encode([
                            [
                                'name' => 'name',
                                'type' => 'text',
                                'label' => 'Imię i nazwisko',
                                'required' => true
                            ],
                            [
                                'name' => 'email',
                                'type' => 'email',
                                'label' => 'Email',
                                'required' => true
                            ],
                            [
                                'name' => 'message',
                                'type' => 'textarea',
                                'label' => 'Wiadomość',
                                'required' => true
                            ]
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    } elseif (is_array($fields)) {
                        $fields = json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    }
                    ?>
                    <textarea name="cf_form_fields" id="cf_form_fields" rows="20" style="width:100%; font-family: monospace; font-size: 13px;"><?php echo esc_textarea($fields); ?></textarea>
                    
                    <p class="description">
                        <strong>Dostępne typy pól:</strong> text, email, tel, url, number, textarea, select, multiselect, radio, checkbox, checkboxes, file, date, time, hidden
                    </p>
                </div>
            <?php elseif ($source_type === 'json_file'): ?>
                <p class="description">Pola formularza są wczytywane z pliku JSON. Edytuj plik aby zmienić definicję pól.</p>
                <?php
                // Podgląd aktualnych pól
                $fields = $field_manager->get_fields($post->ID);
                if (!empty($fields)):
                ?>
                    <div style="margin-top: 15px;">
                        <p><strong>Podgląd zdefiniowanych pól:</strong></p>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Label</th>
                                    <th>Wymagane</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fields as $field): ?>
                                    <tr>
                                        <td><code><?php echo esc_html($field['name']); ?></code></td>
                                        <td><?php echo esc_html($field['type']); ?></td>
                                        <td><?php echo esc_html($field['label']); ?></td>
                                        <td><?php echo $field['required'] ? '✓' : '—'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renderuje metabox z ustawieniami formularza
     */
    public function render_form_settings_meta_box($post)
    {
        $recipient = get_post_meta($post->ID, '_cf_recipient_email', true);
        $subject = get_post_meta($post->ID, '_cf_email_subject', true);
        $enable_recaptcha = get_post_meta($post->ID, '_cf_enable_recaptcha', true);
        
        // Sprawdź czy reCAPTCHA jest skonfigurowana
        $recaptcha_configured = CF_Settings::is_recaptcha_configured();
        ?>
        <p>
            <label for="cf_recipient_email"><strong>Email odbiorcy:</strong></label><br>
            <input type="email" id="cf_recipient_email" name="cf_recipient_email" value="<?php echo esc_attr($recipient); ?>" style="width:100%;">
        </p>
        <p>
            <label for="cf_email_subject"><strong>Temat wiadomości:</strong></label><br>
            <input type="text" id="cf_email_subject" name="cf_email_subject" value="<?php echo esc_attr($subject); ?>" style="width:100%;">
        </p>
        <hr>
        <p>
            <label>
                <input type="checkbox" 
                       name="cf_enable_recaptcha" 
                       id="cf_enable_recaptcha" 
                       value="1" 
                       <?php checked($enable_recaptcha, '1'); ?>
                       <?php echo !$recaptcha_configured ? 'disabled' : ''; ?>>
                <strong>Włącz Google reCAPTCHA</strong>
            </label>
            <?php if (!$recaptcha_configured): ?>
                <br>
                <span class="description" style="color: #d63638;">
                    ⚠ reCAPTCHA nie jest skonfigurowana. 
                    <a href="<?php echo admin_url('edit.php?post_type=cf-form&page=cf-settings'); ?>">Przejdź do ustawień</a>
                </span>
            <?php else: ?>
                <br>
                <span class="description">Zabezpiecz formularz przed spamem za pomocą Google reCAPTCHA v2</span>
            <?php endif; ?>
        </p>
        <?php
    }

    /**
     * Renderuje metabox z widokiem HTML formularza
     */
    public function render_form_view_meta_box($post)
    {
        $form_view = get_post_meta($post->ID, '_cf_form_view', true);
        ?>
        <div class="cf-form-view-wrapper">
            <p class="description">
                Zdefiniuj widok formularza w HTML. Użyj <code>[name_pola]</code> aby wstawić pola formularza.
                <br><strong>Uwaga:</strong> Tagi <code>&lt;script&gt;</code>, <code>&lt;?php</code> i <code>&lt;?=</code> zostaną automatycznie usunięte ze względów bezpieczeństwa.
            </p>
            
            <p>
                <a href="#" onclick="jQuery('#cf-view-example').toggle(); return false;" class="button button-small">Pokaż przykład</a>
            </p>
            
            <div id="cf-view-example" style="display: none; background: #fafafa; padding: 15px; margin: 10px 0; border: 1px solid #ddd;">
                <strong>Przykład widoku HTML:</strong>
                <pre style="margin: 10px 0; font-size: 12px; line-height: 1.6;">&lt;div class="row"&gt;
                    &lt;div class="col-md-6"&gt;
                        &lt;div class="form-group"&gt;
                            [fullname]
                        &lt;/div&gt;
                    &lt;/div&gt;
                    &lt;div class="col-md-6"&gt;
                        &lt;div class="form-group"&gt;
                            [email]
                        &lt;/div&gt;
                    &lt;/div&gt;
                &lt;/div&gt;

                &lt;div class="form-group"&gt;
                    [phone]
                &lt;/div&gt;

                &lt;div class="form-group"&gt;
                    [message]
                &lt;/div&gt;

                &lt;div class="form-group"&gt;
                    [newsletter]
                &lt;/div&gt;</pre>
                <p class="description">
                    Każdy <code>[name_pola]</code> zostanie zastąpiony pełnym HTML pola (label + input/textarea/select/etc.)
                </p>
            </div>

            <?php
            wp_editor($form_view, 'cf_form_view_editor', [
                'textarea_name' => 'cf_form_view',
                'textarea_rows' => 15,
                'media_buttons' => false,
                'teeny' => false,
                'quicktags' => true,
                'tinymce' => [
                    'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,fullscreen',
                    'toolbar2' => '',
                ],
            ]);
            ?>
            
            <p class="description" style="margin-top: 10px;">
                <strong>Dostępne pola:</strong>
                <?php
                $field_manager = new CF_Field_Manager();
                $fields = $field_manager->get_fields($post->ID);
                if (!empty($fields)) {
                    $field_names = array_map(function($field) {
                        return '<code>[' . $field['name'] . ']</code>';
                    }, $fields);
                    echo implode(', ', $field_names);
                } else {
                    echo '<em>Najpierw zdefiniuj pola formularza powyżej</em>';
                }
                ?>
            </p>

            <hr style="margin: 20px 0;">

            <h4>Opcje formularza</h4>
            
            <?php
            $wrapper_classes = get_post_meta($post->ID, '_cf_form_wrapper_classes', true);
            $form_classes = get_post_meta($post->ID, '_cf_form_classes', true);
            $submit_classes = get_post_meta($post->ID, '_cf_submit_classes', true);
            $submit_text = get_post_meta($post->ID, '_cf_submit_text', true);
            if (empty($submit_text)) {
                $submit_text = 'Wyślij';
            }
            ?>

            <p>
                <label for="cf_form_wrapper_classes"><strong>Klasy CSS dla wrappera (.cf-form-wrapper):</strong></label><br>
                <input type="text" id="cf_form_wrapper_classes" name="cf_form_wrapper_classes" value="<?php echo esc_attr($wrapper_classes); ?>" style="width:100%;" placeholder="np. container my-form-container">
                <span class="description">Dodatkowe klasy CSS dla głównego kontenera formularza</span>
            </p>

            <p>
                <label for="cf_form_classes"><strong>Klasy CSS dla formularza (&lt;form&gt;):</strong></label><br>
                <input type="text" id="cf_form_classes" name="cf_form_classes" value="<?php echo esc_attr($form_classes); ?>" style="width:100%;" placeholder="np. needs-validation custom-form">
                <span class="description">Dodatkowe klasy CSS dla elementu &lt;form&gt;</span>
            </p>

            <p>
                <label for="cf_submit_classes"><strong>Klasy CSS dla przycisku Submit:</strong></label><br>
                <input type="text" id="cf_submit_classes" name="cf_submit_classes" value="<?php echo esc_attr($submit_classes); ?>" style="width:100%;" placeholder="np. btn btn-primary btn-lg">
                <span class="description">Dodatkowe klasy CSS dla przycisku wysyłania formularza</span>
            </p>

            <p>
                <label for="cf_submit_text"><strong>Tekst przycisku Submit:</strong></label><br>
                <input type="text" id="cf_submit_text" name="cf_submit_text" value="<?php echo esc_attr($submit_text); ?>" style="width:100%;" placeholder="Wyślij">
                <span class="description">Tekst wyświetlany na przycisku wysyłania formularza (domyślnie: "Wyślij")</span>
            </p>

            <hr style="margin: 20px 0;">

            <h4>Komunikaty formularza</h4>
            <p class="description">Zdefiniuj własne komunikaty sukcesu i błędu dla tego formularza. Jeśli pozostawisz puste, zostaną użyte domyślne komunikaty.</p>
            
            <?php
            $success_message = get_post_meta($post->ID, '_cf_success_message', true);
            $error_message = get_post_meta($post->ID, '_cf_error_message', true);
            $validation_error_message = get_post_meta($post->ID, '_cf_validation_error_message', true);
            ?>

            <p>
                <label for="cf_success_message"><strong>Komunikat sukcesu:</strong></label><br>
                <input type="text" id="cf_success_message" name="cf_success_message" value="<?php echo esc_attr($success_message); ?>" style="width:100%;" placeholder="Formularz został wysłany pomyślnie">
                <span class="description">Komunikat wyświetlany po prawidłowym wysłaniu formularza</span>
            </p>

            <p>
                <label for="cf_error_message"><strong>Komunikat błędu (ogólny):</strong></label><br>
                <input type="text" id="cf_error_message" name="cf_error_message" value="<?php echo esc_attr($error_message); ?>" style="width:100%;" placeholder="Wystąpił błąd podczas wysyłania formularza">
                <span class="description">Komunikat wyświetlany gdy wystąpi błąd po stronie serwera</span>
            </p>

            <p>
                <label for="cf_validation_error_message"><strong>Komunikat błędu walidacji:</strong></label><br>
                <input type="text" id="cf_validation_error_message" name="cf_validation_error_message" value="<?php echo esc_attr($validation_error_message); ?>" style="width:100%;" placeholder="Popraw błędy w formularzu">
                <span class="description">Komunikat wyświetlany gdy formularz zawiera błędy walidacji</span>
            </p>

        </div>
        <?php
    }

    /**
     * Renderuje metabox z szablonem email
     */
    public function render_email_template_meta_box($post)
    {
        $template = get_post_meta($post->ID, '_cf_email_template', true);
        ?>
        <p class="description">Użyj [name_pola] aby wstawić wartości z formularza.</p>
        <textarea name="cf_email_template" rows="10" style="width:100%;"><?php echo esc_textarea($template); ?></textarea>
        <?php
    }

    /**
     * Renderuje metabox z informacją o shortcode
     */
    public function render_shortcode_info_meta_box($post)
    {
        ?>
        <p><strong>Użyj tego shortcode w treści:</strong></p>
        <code>[contact_form id="<?php echo $post->ID; ?>"]</code>
        <p class="description">Skopiuj powyższy shortcode i wklej w treści strony lub wpisu.</p>
        <?php
    }

    /**
     * Renderuje metabox z danymi wysłanego formularza
     */
    public function render_sended_data_meta_box($post)
    {
        $data = get_post_meta($post->ID, '_cf_form_data', true);
        $form_id = get_post_meta($post->ID, '_cf_form_id', true);
        ?>
        <p><strong>ID formularza:</strong> <?php echo esc_html($form_id); ?></p>
        <hr>
        <?php
        if (!empty($data)) {
            echo '<table class="widefat">';
            echo '<thead><tr><th>Pole</th><th>Wartość</th></tr></thead>';
            echo '<tbody>';
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                echo '<tr>';
                echo '<td><strong>' . esc_html($key) . '</strong></td>';
                echo '<td>' . nl2br(esc_html($value)) . '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Brak danych.</p>';
        }
    }

    /**
     * Renderuje metabox z dodatkowymi informacjami
     */
    public function render_sended_meta_meta_box($post)
    {
        $ip = get_post_meta($post->ID, '_cf_sender_ip', true);
        $user_agent = get_post_meta($post->ID, '_cf_user_agent', true);
        ?>
        <p><strong>IP nadawcy:</strong><br><?php echo esc_html($ip); ?></p>
        <p><strong>User Agent:</strong><br><?php echo esc_html($user_agent); ?></p>
        <p><strong>Data wysłania:</strong><br><?php echo get_the_date('Y-m-d H:i:s', $post->ID); ?></p>
        <?php
    }

    /**
     * Zapisuje dane z metaboksów
     */
    public function save_form_meta_boxes($post_id)
    {
        // Sprawdzenie nonce
        if (!isset($_POST['cf_form_meta_box_nonce']) || !wp_verify_nonce($_POST['cf_form_meta_box_nonce'], 'cf_form_meta_box')) {
            return;
        }

        // Sprawdzenie autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Sprawdzenie uprawnień
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Zapisywanie pól
        if (isset($_POST['cf_form_fields'])) {
            update_post_meta($post_id, '_cf_form_fields', sanitize_textarea_field($_POST['cf_form_fields']));
        }

        if (isset($_POST['cf_recipient_email'])) {
            update_post_meta($post_id, '_cf_recipient_email', sanitize_email($_POST['cf_recipient_email']));
        }

        if (isset($_POST['cf_email_subject'])) {
            update_post_meta($post_id, '_cf_email_subject', sanitize_text_field($_POST['cf_email_subject']));
        }

        if (isset($_POST['cf_email_template'])) {
            update_post_meta($post_id, '_cf_email_template', wp_kses_post($_POST['cf_email_template']));
        }

        // Zapisywanie opcji reCAPTCHA
        if (isset($_POST['cf_enable_recaptcha'])) {
            update_post_meta($post_id, '_cf_enable_recaptcha', '1');
        } else {
            delete_post_meta($post_id, '_cf_enable_recaptcha');
        }

        // Zapisywanie opcji formularza (klasy CSS i tekst przycisku)
        if (isset($_POST['cf_form_wrapper_classes'])) {
            update_post_meta($post_id, '_cf_form_wrapper_classes', sanitize_text_field($_POST['cf_form_wrapper_classes']));
        }

        if (isset($_POST['cf_form_classes'])) {
            update_post_meta($post_id, '_cf_form_classes', sanitize_text_field($_POST['cf_form_classes']));
        }

        if (isset($_POST['cf_submit_classes'])) {
            update_post_meta($post_id, '_cf_submit_classes', sanitize_text_field($_POST['cf_submit_classes']));
        }

        if (isset($_POST['cf_submit_text'])) {
            update_post_meta($post_id, '_cf_submit_text', sanitize_text_field($_POST['cf_submit_text']));
        }

        // Zapisywanie komunikatów formularza
        if (isset($_POST['cf_success_message'])) {
            update_post_meta($post_id, '_cf_success_message', sanitize_text_field($_POST['cf_success_message']));
        }

        if (isset($_POST['cf_error_message'])) {
            update_post_meta($post_id, '_cf_error_message', sanitize_text_field($_POST['cf_error_message']));
        }

        if (isset($_POST['cf_validation_error_message'])) {
            update_post_meta($post_id, '_cf_validation_error_message', sanitize_text_field($_POST['cf_validation_error_message']));
        }

        // Zapisywanie widoku formularza z sanityzacją
        if (isset($_POST['cf_form_view'])) {
            $form_view = $_POST['cf_form_view'];
            
            // Usunięcie niebezpiecznych tagów
            $form_view = $this->sanitize_form_view($form_view);
            
            update_post_meta($post_id, '_cf_form_view', $form_view);
        }
    }

    /**
     * Sanityzuje widok HTML formularza
     * Usuwa <script>, <?php ?>, <?= ?>
     * 
     * @param string $html
     * @return string
     */
    private function sanitize_form_view($html)
    {
        // Usunięcie tagów <script>
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        
        /**  Usunięcie '<?php ?>*/
        $html = preg_replace('/<\?php.*?\?>/is', '', $html);
        
        /**  Usunięcie '<?= ?>'*/
        $html = preg_replace('/<\?=.*?\?>/is', '', $html);
        
        /**  Usunięcie '<?'*/
        $html = preg_replace('/<\?(?!xml).*?\?>/is', '', $html);
        
        return $html;
    }
}
