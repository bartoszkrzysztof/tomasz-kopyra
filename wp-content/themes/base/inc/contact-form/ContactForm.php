<?php
/**
 * Contact Form Module
 * 
 * Moduł obsługi formularzy kontaktowych dla WordPress
 */

if (!defined('ABSPATH')) {
    exit;
}

class ContactForm {
    // Singleton instance
    protected static $_instance = null;

    // Ścieżki modułu
    public $plugin_path;
    public $plugin_url;

    // Komponenty modułu
    public $post_types;
    public $shortcode;
    public $rest_api;
    public $mailer;

    private function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Definiuje stałe modułu
     */
    private function define_constants()
    {
        $this->plugin_path = trailingslashit(dirname(__FILE__));
        $this->plugin_url = trailingslashit(get_template_directory_uri() . '/inc/contact-form');
    }

    /**
     * Ładuje pliki modułu
     */
    private function includes()
    {
        require_once $this->plugin_path . 'includes/class-cf-settings.php';
        require_once $this->plugin_path . 'includes/class-cf-field-manager.php';
        require_once $this->plugin_path . 'includes/class-cf-view-parser.php';
        require_once $this->plugin_path . 'includes/class-cf-post-types.php';
        require_once $this->plugin_path . 'includes/class-cf-shortcode.php';
        require_once $this->plugin_path . 'includes/class-cf-rest-api.php';
        require_once $this->plugin_path . 'includes/class-cf-mailer.php';
        require_once $this->plugin_path . 'includes/class-cf-validator.php';
        
        // Ładowanie definicji pól ACF (jeśli ACF jest aktywne)
        if (function_exists('acf_add_local_field_group')) {
            require_once $this->plugin_path . 'includes/acf-fields-definition.php';
        }
    }

    /**
     * Inicjalizuje hooki WordPress
     */
    private function init_hooks()
    {
        add_action('init', [$this, 'init'], 0);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }

    /**
     * Inicjalizuje komponenty modułu
     */
    public function init()
    {
        $this->settings = new CF_Settings();
        $this->post_types = new CF_Post_Types();
        $this->shortcode = new CF_Shortcode();
        $this->rest_api = new CF_Rest_API();
        $this->mailer = new CF_Mailer();
    }

    /**
     * Rejestruje skrypty i style
     */
    public function enqueue_scripts()
    {
        wp_register_script(
            'cf-validation',
            $this->plugin_url . 'assets/js/validation.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_register_style(
            'cf-form-style',
            $this->plugin_url . 'assets/css/form-style.css',
            [],
            '1.0.0'
        );

        // Lokalizacja skryptu
        wp_localize_script('cf-validation', 'cfData', [
            'ajaxUrl' => rest_url('contact-form/v1/validate'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    /**
     * Rejestruje style dla panelu administracyjnego
     */
    public function enqueue_admin_scripts($hook)
    {
        // Ładuj style tylko na stronach edycji formularzy
        global $post_type;
        
        if ($post_type === 'cf-form' || $hook === 'cf-form_page_cf-settings') {
            wp_enqueue_style(
                'cf-admin-style',
                $this->plugin_url . 'assets/css/admin-style.css',
                [],
                '1.0.0'
            );
        }
    }
}

function contactForm()
{
    return ContactForm::instance();
}

$GLOBALS['ContactForm'] = contactForm();