<?php

return [
    'support' => [
        'title-tag',
        'post-thumbnails',
        'html5' => ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption'],
        'editor-styles',
        'wp-block-styles',
        'align-wide',
    ],

    'image_sizes' => [],

    'menus' => [],

    'cleanup' => [
        'remove_emoji' => true,
        'remove_block_library' => false,
        'remove_wp_embed' => false,
        'remove_jquery_migrate' => true,
        'disable_comments' => false,
        'remove_meta_generators' => true,
    ],

    'hide_admin' => [],
    'hide_front' => [],
    'roles_available' => [
        'administrator' => 'Administrator',
        'editor' => 'Redaktor',
        'author' => 'Autor',
        'contributor' => 'Współautor',
        'subscriber' => 'Subskrybent',
    ],
];
