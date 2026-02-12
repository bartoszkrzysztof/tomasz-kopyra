<?php

namespace App\Blocks;

class TextImageBlock
{
    /**
     * Custom render callback dla bloku Hero Banner
     * Dodaje dodatkową logikę biznesową
     *
     * @param array $block Block settings
     * @param string $content Block content
     * @param bool $is_preview Whether in preview mode
     * @param int $post_id Current post ID
     */
    public function __invoke($block, $content = '', $is_preview = false, $post_id = 0)
    {
        // Pobierz pola ACF
        $image = get_field('image') ?: '';
        $text = get_field('text') ?: '';
        $classes = $this->getClasses();

        $data = [
            'block' => $block,
            'is_preview' => $is_preview,
            'post_id' => $post_id,
            'image' => $image,
            'text' => $text,
            'wrapper_classes' => 'text-image-block',
            'image_classes' => implode(' ', $classes['image']),
            'text_classes' => implode(' ', $classes['text']),
        ];

        try {
            echo \Roots\view('blocks.acf.image_text', $data)->render();
        } catch (\Exception $e) {
            if ($is_preview) {
                echo '<div style="padding: 20px; background: #f0f0f0; border: 1px solid #ccc;">';
                echo '<strong>Section error:</strong> ' . esc_html($e->getMessage());
                echo '</div>';
            }
            error_log("Section render error: " . $e->getMessage());
        }
    }

    private function getClasses()
    {
        $classes = [
            'image' => [],
            'text' => [],
        ];

        $direction = get_field('direction') ?: 'image_on_right';
        $ratio = get_field('ratio') ?: '50_50';

        if ($direction === 'image_on_right') {
            $classes['image'][] = 'order-1 md:order-2';
            $classes['text'][] = 'order-2 md:order-1';
        } else {
            $classes['image'][] = 'order-1';
            $classes['text'][] = 'order-2';
        }

        switch ($ratio) {
            case '25_75':
                $classes['image'][] = 'w-full md:w-1/4';
                $classes['text'][] = 'w-full md:w-3/4';
                break;
            case '75_25':
                $classes['image'][] = 'w-full md:w-3/4';
                $classes['text'][] = 'w-full md:w-1/4';
                break;
            case '33_66':
                $classes['image'][] = 'w-full md:w-1/3';
                $classes['text'][] = 'w-full md:w-2/3';
                break;
            case '66_33':
                $classes['image'][] = 'w-full md:w-2/3';
                $classes['text'][] = 'w-full md:w-1/3';
                break;
            default:
                $classes['image'][] = 'w-full md:w-1/2';
                $classes['text'][] = 'w-full md:w-1/2';
        }

        return $classes;
    }
}



    // array(
    //     'key' => 'field_698c2b4f5c37d',
    //     'label' => 'Kierunek',
    //     'name' => 'direction',
    //     'aria-label' => '',
    //     'type' => 'select',
    //     'instructions' => '',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'choices' => array(
    //         'image_on_right' => 'Grafika po prawej',
    //         'image_on_left' => 'Grafika po lewej',
    //     ),
    //     'default_value' => 'image_on_right',
    //     'return_format' => 'value',
    //     'multiple' => 0,
    //     'allow_null' => 0,
    //     'allow_in_bindings' => 0,
    //     'ui' => 0,
    //     'ajax' => 0,
    //     'placeholder' => '',
    //     'create_options' => 0,
    //     'save_options' => 0,
    // ),
    // array(
    //     'key' => 'field_698c2bc75c37e',
    //     'label' => 'Układ',
    //     'name' => 'ratio',
    //     'aria-label' => '',
    //     'type' => 'select',
    //     'instructions' => 'Szerokości komponentów - obraz / tekst',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'choices' => array(
    //         '50_50' => '50% / 50%',
    //         '25_75' => '25% / 75%',
    //         '75_25' => '75% / 25%',
    //         '33_66' => '33% / 66%',
    //         '66_33' => '66% / 33%',
    //     ),
    //     'default_value' => false,
    //     'return_format' => 'value',
    //     'multiple' => 0,
    //     'allow_null' => 0,
    //     'allow_in_bindings' => 0,
    //     'ui' => 0,
    //     'ajax' => 0,
    //     'placeholder' => '',
    //     'create_options' => 0,
    //     'save_options' => 0,
    // ),
    // array(
    //     'key' => 'field_698c2c8898a67',
    //     'label' => 'Ustawienia dodatkowe',
    //     'name' => '',
    //     'aria-label' => '',
    //     'type' => 'accordion',
    //     'instructions' => '',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'open' => 0,
    //     'multi_expand' => 0,
    //     'endpoint' => 0,
    // ),
    // array(
    //     'key' => 'field_698c2cbe98a68',
    //     'label' => 'Dodatkowy odstęp - góra [dektop]',
    //     'name' => 'padding_top_desktop',
    //     'aria-label' => '',
    //     'type' => 'number',
    //     'instructions' => '',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '50',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'default_value' => '',
    //     'min' => '',
    //     'max' => '',
    //     'allow_in_bindings' => 0,
    //     'placeholder' => '',
    //     'step' => '',
    //     'prepend' => '',
    //     'append' => '',
    // ),
    // array(
    //     'key' => 'field_698c2cf998a69',
    //     'label' => 'Dodatkowy odstęp - góra [mobile]',
    //     'name' => 'padding_top_mobile',
    //     'aria-label' => '',
    //     'type' => 'number',
    //     'instructions' => '',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '50',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'default_value' => '',
    //     'min' => '',
    //     'max' => '',
    //     'allow_in_bindings' => 0,
    //     'placeholder' => '',
    //     'step' => '',
    //     'prepend' => '',
    //     'append' => '',
    // ),
    // array(
    //     'key' => 'field_698c2d4298a6a',
    //     'label' => 'Dodatkowy odstęp - dół [dektop]',
    //     'name' => 'padding_bottom_desktop',
    //     'aria-label' => '',
    //     'type' => 'number',
    //     'instructions' => '',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '50',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'default_value' => '',
    //     'min' => '',
    //     'max' => '',
    //     'allow_in_bindings' => 0,
    //     'placeholder' => '',
    //     'step' => '',
    //     'prepend' => '',
    //     'append' => '',
    // ),
    // array(
    //     'key' => 'field_698c2d6998a6b',
    //     'label' => 'Dodatkowy odstęp - dół [mobile]',
    //     'name' => 'padding_bottom_mobile',
    //     'aria-label' => '',
    //     'type' => 'number',
    //     'instructions' => '',
    //     'required' => 0,
    //     'conditional_logic' => 0,
    //     'wrapper' => array(
    //         'width' => '50',
    //         'class' => '',
    //         'id' => '',
    //     ),
    //     'default_value' => '',
    //     'min' => '',
    //     'max' => '',
    //     'allow_in_bindings' => 0,
    //     'placeholder' => '',
    //     'step' => '',
    //     'prepend' => '',
    //     'append' => '',
    // ),