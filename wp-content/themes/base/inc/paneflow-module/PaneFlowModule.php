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
        
        // Process HTML and detect CDN URLs on post save
        add_action('acf/save_post', [$this, 'process_paneflow_post'], 20);
        
        // Register shortcode
        add_shortcode('paneflow', [$this, 'paneflow_shortcode']);
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
     * Process PaneFlow post on save
     * - Extract CSS and JS from HTML
     * - Clean HTML content
     * - Detect CDN URLs from HTML
     * - Create/update media mapping repeater
     * - Replace CDN URLs with local URLs in processed HTML
     * - Save all files to uploads/paneflow/{post_id}/
     */
    public function process_paneflow_post($post_id)
    {
        // Check if this is a paneflow post
        if (get_post_type($post_id) !== 'paneflow') {
            return;
        }

        // Get the original HTML
        $html_original = get_field('paneflow_html_original', $post_id);
        
        if (empty($html_original)) {
            return;
        }

        // Extract inline CSS and JS from HTML
        $extracted_css = $this->extract_inline_css($html_original);
        $extracted_js = $this->extract_inline_js($html_original);

        // Update extracted fields
        update_field('paneflow_extracted_css', $extracted_css, $post_id);
        update_field('paneflow_extracted_js', $extracted_js, $post_id);

        // Clean HTML - remove head, html, body tags and scripts
        $html_cleaned = $this->clean_html_content($html_original);

        // Detect all CDN URLs from cleaned HTML
        $cdn_urls = $this->detect_cdn_urls($html_cleaned);

        // Get existing media mapping
        $existing_mapping = get_field('paneflow_media_mapping', $post_id) ?: [];

        // Build new mapping array, preserving existing selections
        $new_mapping = [];
        foreach ($cdn_urls as $url_data) {
            // Check if this URL already exists in mapping
            $existing_entry = null;
            foreach ($existing_mapping as $entry) {
                if ($entry['cdn_url'] === $url_data['url']) {
                    $existing_entry = $entry;
                    break;
                }
            }

            $new_mapping[] = [
                'cdn_url' => $url_data['url'],
                'media_type' => $url_data['type'],
                'wp_media' => $existing_entry ? $existing_entry['wp_media'] : '',
            ];
        }

        // Update media mapping
        update_field('paneflow_media_mapping', $new_mapping, $post_id);

        // Process HTML with replacements
        $html_processed = $this->process_html_replacements($html_cleaned, $new_mapping);
        
        // Update processed HTML
        update_field('paneflow_html_processed', $html_processed, $post_id);

        // Get user-pasted CSS and JS
        $main_css = get_field('paneflow_css_code', $post_id);
        $main_js = get_field('paneflow_js_code', $post_id);

        // Save all files to uploads/paneflow/{post_id}/
        $this->save_paneflow_files($post_id, [
            'main_css' => $main_css,
            'main_js' => $main_js,
            'extracted_css' => $extracted_css,
            'extracted_js' => $extracted_js,
        ]);
    }

    /**
     * Extract inline CSS from HTML <style> tags
     * Combines all <style> tags into one string
     */
    private function extract_inline_css($html)
    {
        $css_content = '';
        
        // Match all <style> tags and extract their content
        preg_match_all('/<style[^>]*>([\s\S]*?)<\/style>/i', $html, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $style) {
                $css_content .= trim($style) . "\n\n";
            }
        }
        
        return trim($css_content);
    }

    /**
     * Extract inline JavaScript from HTML <script> tags
     * Combines all inline <script> tags (without src attribute) into one string
     */
    private function extract_inline_js($html)
    {
        $js_content = '';
        
        // Match all <script> tags without src attribute (inline only)
        preg_match_all('/<script(?![^>]*\ssrc=)[^>]*>([\s\S]*?)<\/script>/i', $html, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $script) {
                $js_content .= trim($script) . "\n\n";
            }
        }
        
        return trim($js_content);
    }

    /**
     * Clean HTML content
     * - Removes <head> section
     * - Removes <html> and <body> tags
     * - Removes all <script> tags
     * - Returns only the body content
     */
    private function clean_html_content($html)
    {
        // Remove entire <head> section
        $html = preg_replace('/<head[^>]*>([\s\S]*?)<\/head>/i', '', $html);
        
        // Remove all <script> tags
        $html = preg_replace('/<script[^>]*>([\s\S]*?)<\/script>/i', '', $html);
        
        // Extract content from <body> tag if it exists
        if (preg_match('/<body[^>]*>([\s\S]*?)<\/body>/i', $html, $body_match)) {
            $html = $body_match[1];
        }
        
        // Remove <html> and <body> opening/closing tags if still present
        $html = preg_replace('/<\/?html[^>]*>/i', '', $html);
        $html = preg_replace('/<\/?body[^>]*>/i', '', $html);
        
        return trim($html);
    }

    /**
     * Save PaneFlow files to uploads directory
     * Creates: uploads/paneflow/{post_id}/main.css, main.js, extracted.css, extracted.js
     */
    private function save_paneflow_files($post_id, $data)
    {
        // Get WordPress uploads directory
        $upload_dir = wp_upload_dir();
        $paneflow_dir = $upload_dir['basedir'] . '/paneflow/' . $post_id;
        
        // Create directory if it doesn't exist
        if (!file_exists($paneflow_dir)) {
            wp_mkdir_p($paneflow_dir);
        }
        
        // Save main.css (user-pasted CSS)
        if (!empty($data['main_css'])) {
            file_put_contents($paneflow_dir . '/main.css', $data['main_css']);
        }
        
        // Save main.js (user-pasted JS)
        if (!empty($data['main_js'])) {
            file_put_contents($paneflow_dir . '/main.js', $data['main_js']);
        }
        
        // Save extracted.css (from HTML <style> tags)
        if (!empty($data['extracted_css'])) {
            file_put_contents($paneflow_dir . '/extracted.css', $data['extracted_css']);
        }
        
        // Save extracted.js (from HTML <script> tags)
        if (!empty($data['extracted_js'])) {
            file_put_contents($paneflow_dir . '/extracted.js', $data['extracted_js']);
        }
    }

    /**
     * Get URLs to PaneFlow files for enqueuing
     * Returns array with URLs to main.css, main.js, extracted.css, extracted.js
     */
    public function get_paneflow_file_urls($post_id)
    {
        $upload_dir = wp_upload_dir();
        $paneflow_url = $upload_dir['baseurl'] . '/paneflow/' . $post_id;
        $paneflow_dir = $upload_dir['basedir'] . '/paneflow/' . $post_id;
        
        $urls = [];
        
        // Check which files exist and return their URLs
        if (file_exists($paneflow_dir . '/main.css')) {
            $urls['main_css'] = $paneflow_url . '/main.css';
        }
        
        if (file_exists($paneflow_dir . '/main.js')) {
            $urls['main_js'] = $paneflow_url . '/main.js';
        }
        
        if (file_exists($paneflow_dir . '/extracted.css')) {
            $urls['extracted_css'] = $paneflow_url . '/extracted.css';
        }
        
        if (file_exists($paneflow_dir . '/extracted.js')) {
            $urls['extracted_js'] = $paneflow_url . '/extracted.js';
        }
        
        return $urls;
    }

    /**
     * Shortcode handler for [paneflow id="123"]
     * Displays the PaneFlow slider and enqueues necessary CSS/JS files
     */
    public function paneflow_shortcode($atts)
    {
        // Parse shortcode attributes
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts, 'paneflow');

        $post_id = intval($atts['id']);

        // Validate post ID
        if (!$post_id) {
            return '<!-- PaneFlow Error: Missing or invalid ID -->';
        }

        // Check if post exists and is paneflow type
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'paneflow') {
            return '<!-- PaneFlow Error: Post not found or not a PaneFlow type -->';
        }

        // Get processed HTML
        $html_content = get_field('paneflow_html_processed', $post_id);
        
        if (empty($html_content)) {
            return '<!-- PaneFlow Error: No processed HTML content -->';
        }

        // Get file URLs
        $file_urls = $this->get_paneflow_file_urls($post_id);

        // Enqueue CSS files
        if (isset($file_urls['main_css'])) {
            wp_enqueue_style(
                'paneflow-main-' . $post_id,
                $file_urls['main_css'],
                [],
                filemtime(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_urls['main_css']))
            );
        }

        if (isset($file_urls['extracted_css'])) {
            wp_enqueue_style(
                'paneflow-extracted-' . $post_id,
                $file_urls['extracted_css'],
                [],
                filemtime(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_urls['extracted_css']))
            );
        }

        // Enqueue JS files
        if (isset($file_urls['main_js'])) {
            wp_enqueue_script(
                'paneflow-main-' . $post_id,
                $file_urls['main_js'],
                [],
                filemtime(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_urls['main_js'])),
                true
            );
        }

        if (isset($file_urls['extracted_js'])) {
            wp_enqueue_script(
                'paneflow-extracted-' . $post_id,
                $file_urls['extracted_js'],
                [],
                filemtime(str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $file_urls['extracted_js'])),
                true
            );
        }

        // Return HTML wrapped in a container
        return '<div class="paneflow-container" data-paneflow-id="' . esc_attr($post_id) . '">' . $html_content . '</div>';
    }

    /**
     * Detect all CDN URLs from HTML
     * Finds: img src, video src, background-image in style attributes
     */
    private function detect_cdn_urls($html)
    {
        $cdn_domain = 'cdn.paneflow.com';
        $urls = [];
        $found_urls = [];

        // Match img src
        preg_match_all('/<img[^>]+src=["\']([^"\'>]+)["\'][^>]*>/i', $html, $img_matches);
        if (!empty($img_matches[1])) {
            foreach ($img_matches[1] as $url) {
                if (strpos($url, $cdn_domain) !== false && !in_array($url, $found_urls)) {
                    $urls[] = ['url' => $url, 'type' => 'image'];
                    $found_urls[] = $url;
                }
            }
        }

        // Match video src
        preg_match_all('/<video[^>]+src=["\']([^"\'>]+)["\'][^>]*>/i', $html, $video_matches);
        if (!empty($video_matches[1])) {
            foreach ($video_matches[1] as $url) {
                if (strpos($url, $cdn_domain) !== false && !in_array($url, $found_urls)) {
                    $urls[] = ['url' => $url, 'type' => 'video'];
                    $found_urls[] = $url;
                }
            }
        }

        // Match source src inside video tags
        preg_match_all('/<source[^>]+src=["\']([^"\'>]+)["\'][^>]*>/i', $html, $source_matches);
        if (!empty($source_matches[1])) {
            foreach ($source_matches[1] as $url) {
                if (strpos($url, $cdn_domain) !== false && !in_array($url, $found_urls)) {
                    $urls[] = ['url' => $url, 'type' => 'video'];
                    $found_urls[] = $url;
                }
            }
        }

        // Match background-image in style attributes
        preg_match_all('/style=["\'][^"\'>]*background-image:\s*url\(["\']?([^"\')]+)["\']?\)/i', $html, $bg_matches);
        if (!empty($bg_matches[1])) {
            foreach ($bg_matches[1] as $url) {
                if (strpos($url, $cdn_domain) !== false && !in_array($url, $found_urls)) {
                    $urls[] = ['url' => $url, 'type' => 'css-background'];
                    $found_urls[] = $url;
                }
            }
        }

        return $urls;
    }

    /**
     * Process HTML and replace CDN URLs with local URLs
     */
    private function process_html_replacements($html, $mapping)
    {
        $processed_html = $html;

        foreach ($mapping as $entry) {
            $cdn_url = $entry['cdn_url'];
            $wp_media_id = $entry['wp_media'];

            // Skip if no WP media selected
            if (empty($wp_media_id)) {
                continue;
            }

            // Get the WP media URL
            $local_url = wp_get_attachment_url($wp_media_id);

            if ($local_url) {
                // Replace the CDN URL with local URL
                $processed_html = str_replace($cdn_url, $local_url, $processed_html);
            }
        }

        return $processed_html;
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