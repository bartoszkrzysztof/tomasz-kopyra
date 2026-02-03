<?php
/**
 * Definicja pól ACF dla formularzy kontaktowych
 * 
 * Ten plik zawiera programistyczną definicję pól ACF dla CPT 'cf-form'
 * Nie powinien być zmieniany przez administratora, tylko przez developera
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rejestracja pól ACF dla formularzy
 */
if (function_exists('acf_add_local_field_group')) {
    
    acf_add_local_field_group([
        'key' => 'group_cf_form_fields',
        'title' => 'Pola formularza (ACF)',
        'fields' => [
            [
                'key' => 'field_cf_form_fields_repeater',
                'label' => 'Pola formularza',
                'name' => 'cf_form_fields',
                'type' => 'repeater',
                'instructions' => 'Zdefiniuj pola dla tego formularza kontaktowego.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ],
                'collapsed' => 'field_cf_field_name',
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Dodaj pole',
                'sub_fields' => [
                    [
                        'key' => 'field_cf_field_name',
                        'label' => 'Name (identyfikator)',
                        'name' => 'name',
                        'type' => 'text',
                        'instructions' => 'Unikalny identyfikator pola (bez polskich znaków, bez spacji)',
                        'required' => 1,
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_cf_field_label',
                        'label' => 'Label (etykieta)',
                        'name' => 'label',
                        'type' => 'text',
                        'instructions' => 'Etykieta wyświetlana użytkownikowi',
                        'required' => 0,
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_cf_field_accordion',
                        'label' => 'Ustawienia pola',
                        'name' => '',
                        'type' => 'accordion',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'open' => 0,
                        'multi_expand' => 0,
                        'endpoint' => 0,
                    ],
                    [
                        'key' => 'field_cf_field_type',
                        'label' => 'Typ pola',
                        'name' => 'type',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 1,
                        'wrapper' => [
                            'width' => '50',
                        ],
                        'choices' => [
                            'text' => 'Text',
                            'email' => 'Email',
                            'tel' => 'Telefon',
                            'url' => 'URL',
                            'number' => 'Numer',
                            'textarea' => 'Textarea',
                            'select' => 'Select',
                            'multiselect' => 'Multiselect (wielokrotny wybór)',
                            'radio' => 'Radio',
                            'checkbox' => 'Checkbox',
                            'checkboxes' => 'Checkboxes (lista)',
                            'file' => 'Plik',
                            'date' => 'Data',
                            'time' => 'Czas',
                            'hidden' => 'Hidden',
                        ],
                        'default_value' => 'text',
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'ajax' => 0,
                    ],
                    [
                        'key' => 'field_cf_field_placeholder',
                        'label' => 'Placeholder',
                        'name' => 'placeholder',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_cf_field_default_value',
                        'label' => 'Wartość domyślna',
                        'name' => 'default_value',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_cf_field_required',
                        'label' => 'Wymagane',
                        'name' => 'required',
                        'type' => 'true_false',
                        'instructions' => 'Czy pole jest wymagane?',
                        'required' => 0,
                        'wrapper' => [
                            'width' => '50',
                        ],
                        'message' => '',
                        'default_value' => 0,
                        'ui' => 1,
                        'ui_on_text' => 'Tak',
                        'ui_off_text' => 'Nie',
                    ],
                    [
                        'key' => 'field_cf_field_options',
                        'label' => 'Opcje',
                        'name' => 'options',
                        'type' => 'textarea',
                        'instructions' => 'Dla select/radio/checkbox. Format: wartość1:Label 1, wartość2:Label 2',
                        'required' => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'select',
                                ],
                            ],
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'radio',
                                ],
                            ],
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'checkbox',
                                ],
                            ],
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'multiselect',
                                ],
                            ],
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'checkboxes',
                                ],
                            ],
                        ],
                        'wrapper' => [
                            'width' => '100',
                        ],
                        'rows' => 3,
                    ],
                    [
                        'key' => 'field_cf_field_css_classes',
                        'label' => 'Klasy CSS',
                        'name' => 'css_classes',
                        'type' => 'text',
                        'instructions' => 'Dodatkowe klasy CSS oddzielone spacją',
                        'required' => 0,
                        'wrapper' => [
                            'width' => '100',
                        ],
                    ],
                    [
                        'key' => 'field_cf_field_rows',
                        'label' => 'Liczba wierszy',
                        'name' => 'rows',
                        'type' => 'number',
                        'instructions' => 'Dla textarea - liczba wierszy',
                        'required' => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'textarea',
                                ],
                            ],
                        ],
                        'wrapper' => [
                            'width' => '100',
                        ],
                        'default_value' => 5,
                        'min' => 1,
                        'max' => 20,
                    ],
                    [
                        'key' => 'field_cf_field_allowed_types',
                        'label' => 'Dozwolone typy plików',
                        'name' => 'allowed_types',
                        'type' => 'text',
                        'instructions' => 'Rozszerzenia plików oddzielone przecinkiem (np. pdf,doc,docx,jpg)',
                        'required' => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'file',
                                ],
                            ],
                        ],
                        'wrapper' => [
                            'width' => '50',
                        ],
                    ],
                    [
                        'key' => 'field_cf_field_max_size',
                        'label' => 'Maksymalny rozmiar pliku (MB)',
                        'name' => 'max_size',
                        'type' => 'number',
                        'instructions' => 'Maksymalny rozmiar w megabajtach',
                        'required' => 0,
                        'conditional_logic' => [
                            [
                                [
                                    'field' => 'field_cf_field_type',
                                    'operator' => '==',
                                    'value' => 'file',
                                ],
                            ],
                        ],
                        'wrapper' => [
                            'width' => '50',
                        ],
                        'default_value' => 5,
                        'min' => 0.1,
                        'max' => 50,
                        'step' => 0.1,
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'cf-form',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ]);
}
