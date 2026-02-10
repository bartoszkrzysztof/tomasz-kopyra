<?php
/**
 * Domyślna konfiguracja Custom Post Types i taksonomii
 * Można ją nadpisać tworząc plik config-default/cpt.php w motywie potomnym
 * 
 * cpt - tablica zdefiniowanych Custom Post Types
 *  [
 *     'args' => [ // argumenty przekazywane do register_post_type]
 *  ]
 * taxonomies - tablica zdefiniowanych taksonomii
 *  [
 *    'object_type' => ['custom_post_type_slug'], // tablica z CPT do których przypisana jest taksonomia
 *    'args' => [ // argumenty przekazywane do register_taxonomy]
 *  ]
 */

return [
    'cpt' => [
        'paint' => [
            'args' => [
                'label' => 'Obrazy',
                'labels' => [
                    'name' => 'Obrazy',
                    'singular_name' => 'Obraz',
                    'menu_name' => 'Obrazy',
                    'add_new' => 'Dodaj nowy',
                    'add_new_item' => 'Dodaj nowy obraz',
                    'edit_item' => 'Edytuj obraz',
                    'new_item' => 'Nowy obraz',
                    'view_item' => 'Zobacz obraz',
                    'search_items' => 'Szukaj obrazów',
                    'not_found' => 'Nie znaleziono obrazów',
                    'not_found_in_trash' => 'Nie znaleziono obrazów w koszu',
                ],
                'public' => true,
                'has_archive' => true,
                'show_in_rest' => false,
                'supports' => ['title', 'editor', 'thumbnail'],
                'hierarchical' => false,
                'rewrite' => ['slug' => 'swiat-obrazow'],
                'menu_icon' => 'dashicons-format-image',
                'menu_position' => 5,
            ],
        ],
    ],
    'taxonomies' => [
        'paint-category' => [
            'object_type' => ['paint'],
            'args' => [
                'label' => 'Kategorie obrazów',
                'labels' => [
                    'name' => 'Kategorie obrazów',
                    'singular_name' => 'Kategoria obrazu',
                    'menu_name' => 'Kategorie',
                    'all_items' => 'Wszystkie kategorie',
                    'edit_item' => 'Edytuj kategorię',
                    'view_item' => 'Zobacz kategorię',
                    'update_item' => 'Aktualizuj kategorię',
                    'add_new_item' => 'Dodaj nową kategorię',
                    'new_item_name' => 'Nazwa nowej kategorii',
                    'search_items' => 'Szukaj kategorii',
                ],
                'hierarchical' => true,
                'show_in_rest' => true,
                'rewrite' => ['slug' => 'kategorie-obrazu'],
                'show_admin_column' => true,
            ],
        ],
    ],
];