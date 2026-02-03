<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Footer extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'sections.footer',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'footer_main_text' => get_field('footer_headline', 'option') ?: '',
            'footer_social_media_text' => get_field('social_media_headline', 'option') ?: '',
            'footer_online_shop_text' => get_field('online_shop_headline', 'option') ?: '',
            'footer_form_text' => get_field('footer_form_headline', 'option') ?: '',
            'online_shop_link' => $this->online_shop_link(),
            'copyright_text' => $this->copyright_text(),
            'form_shortcode' => get_field('footer_form_shortcode', 'option') ?: '',
            'social_media_links' => get_field('social_media', 'option') ?: [],
            'contact_email' => get_field('contact_email', 'option') ?: '',
        ];
    }

    /**
     * Retrieve the current year.
     */
    public function current_year(): int
    {
        return date('Y');
    }

    /**
     * Retrieve the footer copyright text.
     */
    public function copyright_text(): string
    {
        $copyright = get_field('copyright_text', 'option');
        return sprintf(
            '© %s %s',
            $this->current_year(),
            $copyright
        );
    }

    public function online_shop_link(): string
    {
        $link = get_field('online_shop_url', 'option');
        if (!$link) {
            return '';
        }
        $parsed_link = '<a href="' . esc_url($link['url']) . '" target="' . esc_attr($link['target'] ?: '_self') . '" class="online-shop-link">';

        $image = get_field('online_shop_image', 'option');
        if ($image) {
            $parsed_link .= wp_get_attachment_image($image, 'full', false, ['class' => 'online-shop-image']);
        }
        else {
            $parsed_link .= esc_html($link['title']);
        }
    
        $parsed_link .= '</a>';
        return $parsed_link;
    }
}
