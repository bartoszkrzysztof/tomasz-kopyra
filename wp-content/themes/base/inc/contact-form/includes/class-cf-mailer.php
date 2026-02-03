<?php
/**
 * Obsługa wysyłania emaili
 */

if (!defined('ABSPATH')) {
    exit;
}

class CF_Mailer {

    /**
     * Wysyła email z danymi formularza
     * 
     * @param int $form_id ID formularza
     * @param array $form_data Dane formularza
     * @return bool True jeśli email został wysłany, false w przeciwnym razie
     */
    public function send_form_email($form_id, $form_data)
    {
        // Pobranie ustawień formularza
        $recipient = get_post_meta($form_id, '_cf_recipient_email', true);
        $subject = get_post_meta($form_id, '_cf_email_subject', true);
        $template = get_post_meta($form_id, '_cf_email_template', true);

        // Sprawdzenie czy są niezbędne dane
        if (empty($recipient)) {
            error_log('CF_Mailer: Brak adresu odbiorcy dla formularza ID: ' . $form_id);
            return false;
        }

        // Domyślny temat jeśli nie ustawiony
        if (empty($subject)) {
            $subject = 'Nowa wiadomość z formularza kontaktowego';
        }

        // Parsowanie szablonu email
        $message = $this->parse_email_template($template, $form_data);

        // Jeśli brak szablonu, użyj domyślnego formatu
        if (empty($message)) {
            $message = $this->create_default_message($form_data);
        }

        // Nagłówki email
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        // Dodanie reply-to jeśli email jest w danych formularza
        if (!empty($form_data['email']) && is_email($form_data['email'])) {
            $headers[] = 'Reply-To: ' . $form_data['email'];
        }

        // Hook pozwalający na modyfikację parametrów emaila
        $email_params = apply_filters('cf_email_params', [
            'to' => $recipient,
            'subject' => $subject,
            'message' => $message,
            'headers' => $headers,
        ], $form_id, $form_data);

        // Wysłanie emaila przez wp_mail()
        $sent = wp_mail(
            $email_params['to'],
            $email_params['subject'],
            $email_params['message'],
            $email_params['headers']
        );

        // Logowanie jeśli wysyłka się nie powiodła
        if (!$sent) {
            error_log('CF_Mailer: Błąd podczas wysyłania emaila dla formularza ID: ' . $form_id);
        }

        return $sent;
    }

    /**
     * Parsuje szablon email, zastępując [name_pola] wartościami z formularza
     * 
     * @param string $template Szablon email
     * @param array $form_data Dane formularza
     * @return string Sparsowany szablon
     */
    private function parse_email_template($template, $form_data)
    {
        if (empty($template)) {
            return '';
        }

        // Znalezienie wszystkich [name_pola] w szablonie
        preg_match_all('/\[([^\]]+)\]/', $template, $matches);

        if (empty($matches[1])) {
            return $template;
        }

        // Zastąpienie każdego [name_pola] wartością z form_data
        foreach ($matches[1] as $field_name) {
            $value = '';
            
            if (isset($form_data[$field_name])) {
                $value = $form_data[$field_name];
                
                // Jeśli wartość jest tablicą (np. checkbox), zamień na string
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                
                // Sanityzacja wartości
                $value = wp_kses_post($value);
            }
            
            // Zastąpienie w szablonie
            $template = str_replace('[' . $field_name . ']', $value, $template);
        }

        // Konwersja nowych linii na HTML
        $template = nl2br($template);

        return $template;
    }

    /**
     * Tworzy domyślną wiadomość z danych formularza
     * 
     * @param array $form_data Dane formularza
     * @return string HTML wiadomości
     */
    private function create_default_message($form_data)
    {
        $message = '<html><body>';
        $message .= '<h2>Nowa wiadomość z formularza kontaktowego</h2>';
        $message .= '<table style="border-collapse: collapse; width: 100%;">';

        foreach ($form_data as $key => $value) {
            // Pomiń ukryte pola systemowe
            if (strpos($key, '_') === 0) {
                continue;
            }

            // Jeśli wartość jest tablicą
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $label = ucfirst(str_replace('_', ' ', $key));
            $message .= sprintf(
                '<tr><td style="border: 1px solid #ddd; padding: 8px;"><strong>%s:</strong></td><td style="border: 1px solid #ddd; padding: 8px;">%s</td></tr>',
                esc_html($label),
                nl2br(esc_html($value))
            );
        }

        $message .= '</table>';
        $message .= '<p style="margin-top: 20px; color: #666; font-size: 12px;">Data wysłania: ' . current_time('Y-m-d H:i:s') . '</p>';
        $message .= '</body></html>';

        return $message;
    }

    /**
     * Wysyła email z potwierdzeniem do nadawcy (opcjonalne)
     * 
     * @param string $recipient_email Email odbiorcy
     * @param string $subject Temat wiadomości
     * @param string $message Treść wiadomości
     * @return bool
     */
    public function send_confirmation_email($recipient_email, $subject, $message)
    {
        if (!is_email($recipient_email)) {
            return false;
        }

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
        ];

        return wp_mail($recipient_email, $subject, $message, $headers);
    }
}
