<?php
/**
 * Domyślna konfiguracja Custom Post Types i taksonomii
 * Można ją nadpisać tworząc plik config-default/cpt.php w motywie potomnym
 * 
 * cpt - tablica zdefiniowanych Custom Post Types
 *  'slug' => [
 *     'args' => [ // argumenty przekazywane do register_post_type]
 *  ]
 * taxonomies - tablica zdefiniowanych taksonomii
 *  'slug' => [
 *    'object_type' => ['custom_post_type_slug'], // tablica z CPT do których przypisana jest taksonomia
 *    'args' => [ // argumenty przekazywane do register_taxonomy]
 *  ]
 */
return [
    'cpt' => [],
    'taxonomies' => [],
];