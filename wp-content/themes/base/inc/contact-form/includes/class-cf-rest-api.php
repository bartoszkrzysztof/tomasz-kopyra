<?php
/**
 * REST API dla walidacji formularzy
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Rest_API {

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Rejestruje endpointy REST API
     */
    public function register_routes()
    {
        register_rest_route('contact-form/v1', '/validate', [
            'methods' => 'POST',
            'callback' => [$this, 'validate_form'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('contact-form/v1', '/submit', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_form'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Waliduje dane formularza
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function validate_form($request)
    {
        $form_id = intval($request->get_param('form_id'));
        $form_data = $request->get_param('form_data');

        if (!$form_id || !$form_data) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Brak wymaganych danych',
            ], 400);
        }

        // Pobranie konfiguracji formularza przez Field Manager
        $field_manager = new CF_Field_Manager();
        $fields_config = $field_manager->get_fields($form_id);

        // Walidacja
        $validator = new CF_Validator();
        $validation_result = $validator->validate($form_data, $fields_config);

        if ($validation_result['valid']) {
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Walidacja przebiegła pomyślnie',
            ], 200);
        } else {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Błędy walidacji',
                'errors' => $validation_result['errors'],
            ], 400);
        }
    }

    /**
     * Obsługuje wysłanie formularza
     * 
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function submit_form($request)
    {
        $form_id = intval($request->get_param('form_id'));
        $form_data = $request->get_param('form_data');
        $nonce = $request->get_param('nonce');

        // Weryfikacja nonce
        if (!wp_verify_nonce($nonce, 'cf_form_submit_' . $form_id)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Błąd bezpieczeństwa',
            ], 403);
        }

        // Walidacja
        $field_manager = new CF_Field_Manager();
        $fields_config = $field_manager->get_fields($form_id);

        $validator = new CF_Validator();
        $validation_result = $validator->validate($form_data, $fields_config, $_FILES, $form_id);

        if (!$validation_result['valid']) {
            // Pobranie własnego komunikatu walidacji lub użycie domyślnego
            $validation_error_msg = get_post_meta($form_id, '_cf_validation_error_message', true);
            if (empty($validation_error_msg)) {
                $validation_error_msg = 'Popraw błędy w formularzu';
            }
            
            return new WP_REST_Response([
                'success' => false,
                'message' => $validation_error_msg,
                'errors' => $validation_result['errors'],
            ], 400);
        }

        // Zapis wysłanego formularza
        $sended_id = $this->save_sended_form($form_id, $form_data);

        if (!$sended_id) {
            // Pobranie własnego komunikatu błędu lub użycie domyślnego
            $error_msg = get_post_meta($form_id, '_cf_error_message', true);
            if (empty($error_msg)) {
                $error_msg = 'Błąd podczas zapisywania formularza';
            }
            
            return new WP_REST_Response([
                'success' => false,
                'message' => $error_msg,
            ], 500);
        }

        // Wysłanie emaila
        $mailer = new CF_Mailer();
        $email_sent = $mailer->send_form_email($form_id, $form_data);

        if (!$email_sent) {
            // Pobranie własnego komunikatu błędu lub użycie domyślnego
            $error_msg = get_post_meta($form_id, '_cf_error_message', true);
            if (empty($error_msg)) {
                $error_msg = 'Formularz został zapisany, ale wystąpił błąd podczas wysyłania emaila';
            }
            
            return new WP_REST_Response([
                'success' => false,
                'message' => $error_msg,
            ], 500);
        }

        // Pobranie własnego komunikatu sukcesu lub użycie domyślnego
        $success_msg = get_post_meta($form_id, '_cf_success_message', true);
        if (empty($success_msg)) {
            $success_msg = 'Formularz został wysłany pomyślnie';
        }

        return new WP_REST_Response([
            'success' => true,
            'message' => $success_msg,
        ], 200);
    }

    /**
     * Zapisuje wysłany formularz jako wpis cf-sended
     * 
     * @param int $form_id ID formularza
     * @param array $form_data Dane formularza
     * @return int|false ID utworzonego wpisu lub false w przypadku błędu
     */
    private function save_sended_form($form_id, $form_data)
    {
        $form = get_post($form_id);
        if (!$form) {
            return false;
        }

        // Utworzenie tytułu na podstawie formularza i daty
        $title = sprintf(
            '%s - %s',
            $form->post_title,
            current_time('Y-m-d H:i:s')
        );

        // Utworzenie wpisu
        $post_id = wp_insert_post([
            'post_type' => 'cf-sended',
            'post_title' => $title,
            'post_status' => 'publish',
            'post_author' => 1,
        ]);

        if (is_wp_error($post_id)) {
            return false;
        }

        // Zapisanie danych meta
        update_post_meta($post_id, '_cf_form_id', $form_id);
        update_post_meta($post_id, '_cf_form_data', $form_data);
        update_post_meta($post_id, '_cf_sender_ip', $this->get_user_ip());
        update_post_meta($post_id, '_cf_user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');

        return $post_id;
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
}
