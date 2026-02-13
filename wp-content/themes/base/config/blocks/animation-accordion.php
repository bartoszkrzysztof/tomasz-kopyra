<?php 
return [
    'enabled' => true,
    'callback' => \App\Blocks\AnimationAccordionBlock::class,
    'block_definition' => [
        'name' => 'animation-accordion',
        'title' => 'Animacja - panele rozsuwane',
        'description' => '',
        'category' => 'theme',
        'icon' => 'move',
        'keywords' => ['animation', 'accordion', 'content'],
        'mode' => 'edit',
        'supports' => [
            'align' => false,
            'anchor' => true,
            'customClassName' => true,
            'jsx' => true,
        ],
    ],
    'fields' => include_once __DIR__ . '/animation-accordion-fields.php',
];