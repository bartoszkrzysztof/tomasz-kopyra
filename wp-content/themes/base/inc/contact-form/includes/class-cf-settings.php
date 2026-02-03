<?php
/**
 * Settings Page dla modułu Contact Form
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Settings {

    /**
     * Option name dla ustawień
     */
    const OPTION_NAME = 'cf_module_settings';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Dodaje stronę ustawień do menu
     */
    public function add_settings_page()
    {
        add_submenu_page(
            'edit.php?post_type=cf-form',
            'Ustawienia modułu',
            'Ustawienia',
            'manage_options',
            'cf-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * Rejestruje ustawienia modułu
     */
    public function register_settings()
    {
        register_setting(
            'cf_settings_group',
            self::OPTION_NAME,
            [$this, 'sanitize_settings']
        );

        // Sekcja: Google reCAPTCHA
        add_settings_section(
            'cf_recaptcha_section',
            'Google reCAPTCHA v2',
            [$this, 'render_recaptcha_section'],
            'cf-settings'
        );

        // Pole: reCAPTCHA Site Key
        add_settings_field(
            'recaptcha_site_key',
            'Site Key',
            [$this, 'render_site_key_field'],
            'cf-settings',
            'cf_recaptcha_section'
        );

        // Pole: reCAPTCHA Secret Key
        add_settings_field(
            'recaptcha_secret_key',
            'Secret Key',
            [$this, 'render_secret_key_field'],
            'cf-settings',
            'cf_recaptcha_section'
        );

        // Sekcja: Tłumaczenia komunikatów walidacyjnych
        add_settings_section(
            'cf_translations_section',
            'Tłumaczenia komunikatów',
            [$this, 'render_translations_section'],
            'cf-settings'
        );

        // Pole: Tabela tłumaczeń
        add_settings_field(
            'message_translations',
            'Komunikaty walidacyjne',
            [$this, 'render_translations_field'],
            'cf-settings',
            'cf_translations_section'
        );
    }

    /**
     * Renderuje stronę ustawień
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Sprawdź czy zapisano ustawienia
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'cf_messages',
                'cf_message',
                'Ustawienia zostały zapisane',
                'updated'
            );
        }

        settings_errors('cf_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('cf_settings_group');
                do_settings_sections('cf-settings');
                submit_button('Zapisz ustawienia');
                ?>
            </form>
            
            <div class="card" style="max-width: 800px; margin-top: 20px;">
                <h2>Jak uzyskać klucze Google reCAPTCHA?</h2>
                <ol>
                    <li>Przejdź na stronę: <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a></li>
                    <li>Zaloguj się na konto Google</li>
                    <li>Kliknij przycisk "+" aby dodać nową witrynę</li>
                    <li>Wybierz <strong>reCAPTCHA v2</strong> → <strong>"Nie jestem robotem" Checkbox</strong></li>
                    <li>Wpisz domenę swojej witryny</li>
                    <li>Skopiuj otrzymane klucze i wklej powyżej</li>
                </ol>
                <p class="description">
                    <strong>Uwaga:</strong> Po zapisaniu kluczy, możesz włączyć reCAPTCHA dla poszczególnych formularzy w ich ustawieniach.
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Renderuje opis sekcji reCAPTCHA
     */
    public function render_recaptcha_section()
    {
        echo '<p>Skonfiguruj klucze Google reCAPTCHA v2 aby zabezpieczyć formularze przed spamem.</p>';
    }

    /**
     * Renderuje pole Site Key
     */
    public function render_site_key_field()
    {
        $options = get_option(self::OPTION_NAME, []);
        $value = isset($options['recaptcha_site_key']) ? $options['recaptcha_site_key'] : '';
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_NAME; ?>[recaptcha_site_key]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="6Lc...">
        <p class="description">Klucz publiczny (Site Key) z panelu Google reCAPTCHA</p>
        <?php
    }

    /**
     * Renderuje pole Secret Key
     */
    public function render_secret_key_field()
    {
        $options = get_option(self::OPTION_NAME, []);
        $value = isset($options['recaptcha_secret_key']) ? $options['recaptcha_secret_key'] : '';
        ?>
        <input type="text" 
               name="<?php echo self::OPTION_NAME; ?>[recaptcha_secret_key]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text"
               placeholder="6Lc...">
        <p class="description">Klucz tajny (Secret Key) z panelu Google reCAPTCHA</p>
        <?php
    }

    /**
     * Renderuje opis sekcji tłumaczeń
     */
    public function render_translations_section()
    {
        // Sprawdzenie czy jest dostępny plugin wielojęzyczności
        $has_multilang = $this->has_multilang_plugin();
        
        if (!$has_multilang) {
            echo '<p class="description" style="color: #d63638;">';
            echo '<strong>Uwaga:</strong> Aby używać tłumaczeń, zainstaluj i aktywuj Polylang lub WPML.';
            echo '</p>';
            return;
        }

        echo '<p>Skonfiguruj komunikaty walidacyjne dla różnych języków. ';
        echo 'Jeśli tłumaczenie nie jest zdefiniowane, zostanie użyte domyślne (polskie).</p>';
        
        $languages = $this->get_available_languages();
        if (!empty($languages)) {
            echo '<p class="description">Dostępne języki: <strong>' . implode(', ', array_map('strtoupper', $languages)) . '</strong></p>';
        }
    }

    /**
     * Renderuje pole tłumaczeń
     */
    public function render_translations_field()
    {
        $has_multilang = $this->has_multilang_plugin();
        
        if (!$has_multilang) {
            echo '<p class="description">Funkcja tłumaczeń jest niedostępna bez pluginu wielojęzyczności.</p>';
            return;
        }

        $options = get_option(self::OPTION_NAME, []);
        $translations = isset($options['message_translations']) ? $options['message_translations'] : [];
        $languages = $this->get_available_languages();
        
        // Pobierz aktualny język w panelu administracyjnym
        $current_lang = $this->get_admin_language();
        
        // Definicje komunikatów
        $messages = [
            'field_required' => 'Pole wymagane',
            'invalid_email' => 'Błędny email',
            'invalid_url' => 'Błędny URL',
            'invalid_number' => 'Błędna liczba',
            'invalid_phone' => 'Błędny telefon',
            'file_too_large' => 'Plik za duży',
            'file_type_not_allowed' => 'Niedozwolony typ pliku',
            'file_upload_error' => 'Błąd uploadu',
            'recaptcha_missing' => 'Brak reCAPTCHA',
            'recaptcha_verification_error' => 'Błąd weryfikacji reCAPTCHA',
            'recaptcha_verification_failed' => 'Weryfikacja reCAPTCHA nie powiodła się',
        ];
        
        // Domyślne wartości (polskie)
        $defaults = [
            'field_required' => 'Pole: %s jest wymagane',
            'invalid_email' => 'Pole: %s musi zawierać poprawny adres email',
            'invalid_url' => 'Pole: %s musi zawierać poprawny adres URL',
            'invalid_number' => 'Pole: %s musi zawierać liczbę',
            'invalid_phone' => 'Pole: %s musi zawierać poprawny numer telefonu',
            'file_too_large' => 'Plik jest za duży. Maksymalny rozmiar to %s MB',
            'file_type_not_allowed' => 'Niedozwolony typ pliku. Dozwolone typy: %s',
            'file_upload_error' => 'Wystąpił błąd podczas przesyłania pliku',
            'recaptcha_missing' => 'Proszę potwierdzić, że nie jesteś robotem',
            'recaptcha_verification_error' => 'Błąd weryfikacji reCAPTCHA. Spróbuj ponownie.',
            'recaptcha_verification_failed' => 'Weryfikacja reCAPTCHA nie powiodła się. Spróbuj ponownie.',
        ];
        
        ?>
        <div class="cf-translations-wrapper">
            <style>
                .cf-translations-table {
                    width: 100%;
                    max-width: 800px;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .cf-translations-table th,
                .cf-translations-table td {
                    padding: 10px;
                    border: 1px solid #ddd;
                    text-align: left;
                }
                .cf-translations-table th {
                    background-color: #f5f5f5;
                    font-weight: 600;
                }
                .cf-translations-table .message-key {
                    width: 200px;
                    font-weight: 500;
                    background-color: #f9f9f9;
                }
                .cf-translations-table input[type="text"] {
                    width: 100%;
                    min-width: 400px;
                }
                .cf-translations-table .default-value {
                    font-size: 0.9em;
                    color: #666;
                    font-style: italic;
                    display: block;
                    margin-top: 3px;
                }
                .cf-current-language-info {
                    background: #e7f3ff;
                    border-left: 4px solid #0073aa;
                    padding: 12px;
                    margin-bottom: 15px;
                }
                .cf-current-language-info strong {
                    color: #0073aa;
                }
            </style>
            
            <div class="cf-current-language-info">
                <strong>Edytujesz tłumaczenia dla języka: <?php echo esc_html(strtoupper($current_lang)); ?></strong>
                <p style="margin: 5px 0 0 0; font-size: 13px;">
                    Aby edytować tłumaczenia dla innego języka, przełącz język w panelu administracyjnym 
                    <?php if (function_exists('pll_current_language')): ?>
                        (górny pasek administratora - selektor języka Polylang).
                    <?php elseif (defined('ICL_LANGUAGE_CODE')): ?>
                        (górny pasek administratora - selektor języka WPML).
                    <?php endif; ?>
                </p>
            </div>
            
            <table class="cf-translations-table">
                <thead>
                    <tr>
                        <th class="message-key">Komunikat</th>
                        <th><?php echo esc_html(strtoupper($current_lang)); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $key => $label): ?>
                        <tr>
                            <td class="message-key">
                                <?php echo esc_html($label); ?>
                                <span class="default-value"><?php echo esc_html($defaults[$key]); ?></span>
                            </td>
                            <td>
                                <?php
                                $value = isset($translations[$current_lang][$key]) ? $translations[$current_lang][$key] : '';
                                $placeholder = ($current_lang === 'pl') ? $defaults[$key] : '';
                                ?>
                                <input type="text" 
                                       name="<?php echo self::OPTION_NAME; ?>[message_translations][<?php echo $current_lang; ?>][<?php echo $key; ?>]" 
                                       value="<?php echo esc_attr($value); ?>"
                                       placeholder="<?php echo esc_attr($placeholder); ?>" />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php
            // Ukryte pola dla innych języków, aby zachować ich wartości
            foreach ($languages as $lang) {
                if ($lang !== $current_lang && isset($translations[$lang])) {
                    foreach ($translations[$lang] as $key => $value) {
                        echo '<input type="hidden" name="' . self::OPTION_NAME . '[message_translations][' . $lang . '][' . $key . ']" value="' . esc_attr($value) . '" />';
                    }
                }
            }
            ?>
            
            <p class="description" style="margin-top: 15px;">
                <strong>Uwaga:</strong> Używaj <code>%s</code> jako placeholder dla dynamicznych wartości (np. nazwa pola, rozmiar pliku).
            </p>
        </div>
        <?php
    }

    /**
     * Sprawdza czy jest zainstalowany plugin wielojęzyczności
     * 
     * @return bool
     */
    private function has_multilang_plugin()
    {
        // Polylang
        if (function_exists('pll_current_language')) {
            return true;
        }
        
        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            return true;
        }
        
        return false;
    }

    /**
     * Pobiera listę dostępnych języków
     * 
     * @return array
     */
    private function get_available_languages()
    {
        $languages = [];
        
        // Polylang
        if (function_exists('pll_languages_list')) {
            $languages = pll_languages_list();
        }
        
        // WPML
        if (function_exists('icl_get_languages')) {
            $wpml_languages = icl_get_languages('skip_missing=0');
            if (is_array($wpml_languages)) {
                $languages = array_keys($wpml_languages);
            }
        }
        
        // Fallback
        if (empty($languages)) {
            $languages = ['pl', 'en'];
        }
        
        return $languages;
    }

    /**
     * Pobiera aktualny język w panelu administracyjnym
     * 
     * @return string
     */
    private function get_admin_language()
    {
        // Polylang - język admin
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language('admin');
            if ($lang) {
                return $lang;
            }
        }
        
        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        }
        
        // Fallback - pierwszy dostępny język
        $languages = $this->get_available_languages();
        return !empty($languages) ? $languages[0] : 'pl';
    }
    public function sanitize_settings($input)
    {
        $sanitized = [];

        if (isset($input['recaptcha_site_key'])) {
            $sanitized['recaptcha_site_key'] = sanitize_text_field($input['recaptcha_site_key']);
        }

        if (isset($input['recaptcha_secret_key'])) {
            $sanitized['recaptcha_secret_key'] = sanitize_text_field($input['recaptcha_secret_key']);
        }

        // Sanityzacja tłumaczeń
        if (isset($input['message_translations']) && is_array($input['message_translations'])) {
            $sanitized['message_translations'] = [];
            
            foreach ($input['message_translations'] as $lang => $messages) {
                $lang = sanitize_key($lang);
                $sanitized['message_translations'][$lang] = [];
                
                if (is_array($messages)) {
                    foreach ($messages as $key => $value) {
                        $key = sanitize_key($key);
                        $sanitized['message_translations'][$lang][$key] = sanitize_text_field($value);
                    }
                }
            }
        }

        return $sanitized;
    }

    /**
     * Pobiera wartość ustawienia
     */
    public static function get_option($key, $default = '')
    {
        $options = get_option(self::OPTION_NAME, []);
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Sprawdza czy reCAPTCHA jest skonfigurowana
     */
    public static function is_recaptcha_configured()
    {
        $site_key = self::get_option('recaptcha_site_key');
        $secret_key = self::get_option('recaptcha_secret_key');
        
        return !empty($site_key) && !empty($secret_key);
    }

    /**
     * Pobiera Site Key
     */
    public static function get_site_key()
    {
        return self::get_option('recaptcha_site_key');
    }

    /**
     * Pobiera Secret Key
     */
    public static function get_secret_key()
    {
        return self::get_option('recaptcha_secret_key');
    }

    /**
     * Pobiera tłumaczenia komunikatów dla danego języka
     * 
     * @param string $lang Kod języka
     * @return array
     */
    public static function get_message_translations($lang = 'pl')
    {
        $options = get_option(self::OPTION_NAME, []);
        $translations = isset($options['message_translations'][$lang]) ? $options['message_translations'][$lang] : [];
        
        // Filtruj puste wartości
        return array_filter($translations, function($value) {
            return !empty($value);
        });
    }
}
