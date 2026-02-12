<?php

class PaneFlowModule
{
    /**
     * The single instance of the class
     *
     * @var PaneFlowModule|null
     */
    private static $instance = null;

    /**
     * Make constructor private to prevent direct creation of object
     */
    private function __construct()
    {
        add_action('init', [$this, 'register_cpt']);
        $this->register_acf_fields();
        
        add_filter('upload_mimes', [$this, 'add_custom_mime_types']);
        add_filter('wp_check_filetype_and_ext', [$this, 'check_filetype'], 10, 5);
    }

    public function register_cpt()
    {
        $args = [
            'label' => 'PaneFlow',
            'labels' => [
                'name' => 'PaneFlow',
                'singular_name' => 'Galerie PaneFlow',
                'menu_name' => 'PaneFlow',
                'add_new' => 'Dodaj nową',
                'add_new_item' => 'Dodaj nową',
                'edit_item' => 'Edytuj',
                'new_item' => 'Nowa',
                'view_item' => 'Zobacz',
                'search_items' => 'Szukaj',
                'not_found' => 'Nie znaleziono',
                'not_found_in_trash' => 'Nie znaleziono w koszu',
            ],
            'public' => false,
            'show_ui' => true,
            'has_archive' => true,
            'show_in_rest' => false,
            'supports' => ['title'],
            'hierarchical' => false,
            'menu_icon' => 'dashicons-welcome-widgets-menus',
            'menu_position' => 5,
        ];

        register_post_type('paneflow', $args);
    }

    public function register_acf_fields()
    {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'group_paneflow',
                'title' => 'PaneFlow Fields',
                'fields' => include_once __DIR__ . '/assets/paneflow-fields.php',
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'paneflow',
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Add custom MIME types for CSS, JS, and HTML files
     * Only for specific ACF fields
     */
    public function add_custom_mime_types($mimes)
    {
        // Only allow for specific ACF fields
        if (!$this->is_allowed_acf_field()) {
            return $mimes;
        }

        $mimes['css'] = 'text/css';
        $mimes['js'] = 'application/javascript';
        $mimes['html'] = 'text/html';
        $mimes['htm'] = 'text/html';

        return $mimes;
    }

    /**
     * Check file type and extension
     * Allow CSS, JS, HTML only for specific ACF fields
     */
    public function check_filetype($data, $file, $filename, $mimes, $real_mime)
    {
        // Only allow for specific ACF fields
        if (!$this->is_allowed_acf_field()) {
            return $data;
        }

        $wp_file_type = wp_check_filetype($filename, $mimes);
        $ext = $wp_file_type['ext'];
        $type = $wp_file_type['type'];

        if (in_array($ext, ['css', 'js', 'html', 'htm'])) {
            $data['ext'] = $ext;
            $data['type'] = $type;
            $data['proper_filename'] = $filename;
        }

        return $data;
    }

    /**
     * Check if current upload is from allowed ACF field
     */
    private function is_allowed_acf_field()
    {
        $allowed_fields = ['paneflow_style_css', 'paneflow_script_js', 'paneflow_html_file'];

        // Check for ACF uploader field
        if (isset($_POST['_acfuploader'])) {
            $field = sanitize_text_field($_POST['_acfuploader']);
            return in_array($field, $allowed_fields);
        }

        // Alternative check for field key in request
        if (isset($_REQUEST['field'])) {
            $field_key = sanitize_text_field($_REQUEST['field']);
            
            // Get ACF field object by key
            if (function_exists('acf_get_field')) {
                $field_object = acf_get_field($field_key);
                if ($field_object && isset($field_object['name'])) {
                    return in_array($field_object['name'], $allowed_fields);
                }
            }
        }

        return false;
    }

    /**
     * Returns the single instance of the class
     *
     * @return PaneFlowModule
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserializing
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}

/**
 * Bootstrap the PaneFlowModule on init hook.
 * Using 'init' ensures WordPress core, plugins, and theme are fully loaded
 * before registering hooks and actions.
 */
function paneflow_module_init()
{
    PaneFlowModule::get_instance();
}

add_action('init', 'paneflow_module_init', 0);