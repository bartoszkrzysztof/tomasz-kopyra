<?php

/**
 * Theme configuration
 * 
 * Nadpisuje bazową konfigurację z packages/sage-base/config/theme.php
 */

return [
    'menus' => [
        'primary' => 'Nawigacja główna',
        'footer' => 'Nawigacja stopka',
    ],

    'cleanup' => [
        'remove_emoji' => true,
        'remove_block_library' => false,
        'remove_wp_embed' => true,
        'remove_jquery_migrate' => true,
        'disable_comments' => true,
        'remove_meta_generators' => true,
    ],

    'image_sizes' => [
        'gallery-thumb' => [ 328, 'auto', false ],
    ],
    'hide_admin' => ['post', 'comment'],
    'roles_available' => [
        'administrator' => 'Administrator',
        'editor' => 'Edytor',
        'author' => false,
        'contributor' => false,
        'subscriber' => 'Klient',
    ],
];
