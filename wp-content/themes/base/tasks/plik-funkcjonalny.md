# Zadanie
Implementacja szablonu WP zgodnie z wytycznymi.

## Stack
1. To jest Roots Sage - nowoczesny WordPress theme framework oparty o Laravel Blade i Vite
2. Tailwind CSS v4 - używany via @tailwindcss/vite, nie tradycyjny PostCSS
3. Vite jako bundler - nie Webpack, entry points w vite.config.js
4. Blade templates - nie tradycyjne PHP WordPress templates
5. Service Providers - Laravel-style architecture
6. Custom Properties - zmienne container definiowane jako --container-*
7. Dev URL: localhost:5173 (Vite) + WordPress na Local (virtual host: http://tomasz-kopera.local/)
8. Debug aktywny - logi w wp-content/debug.log 
9. WordPress: Instalacja lokalna (Local by Flywheel)
10. Package Manager: npm/Composer

## File Structure
```
config/
├── acf-blocks.php (acf blocks definitions)
├── app.php (app config)
├── blocks.php (blocks allowed)
├── cpt.php (cpt config)
└── theme.php (theme config)
inc/
├── test-app/ (example custom function folder)
│   ├── test-app.php (example custom function file)
└── inc-bootstrap.php (custom functions loader)
resources/
├── styles/
│   ├── app.scss (main entry)
│   ├── base/
│   ├── components/
│   └── layouts/
├── scripts/
│   ├── app.js (main entry)
│   └── components/
└── views/
    ├── blocks/ (ACF blocks)
    ├── components/ (reusable)
    └── layouts/ (page templates)
```
## Naming Conventions
- Blocks: `acf-block-{name}`
- Components: `component-{name}`
- CSS classes: BEM methodology
- Files: kebab-case

## Założenia do stylu pracy
1. Atomizacja komponentów, klas, bazowych skryptów js i styli (scss i zagnieżdżanie)
2. folder app zawiera core funkcjonalności, jeśli nie jest to konieczne dla struktury, to nie powinien być zmieniany
3. dodatkowe moduły na potrzeby szablonu powinny być definiowane w inc/ + folder oraz załączane w pliku inc/inc-bootstrap.php
4. css + tailwind - jeśli to możliwe tworzymy klasy css rozbudowane o @apply klas tailwind. Klasy tailwind używane tylko do modyfikacji szkieletu.
5. rwd i breakpointy - trzymaj się klas i breakpointów tailwind -

## Design Tokens
### Struktura
Folder `base/` zawiera podstawowe style dla całego projektu:
- `_typography.scss` - definicje typografii (h1-h6, p, ul, ol)
- `_colors.scss` - utility classes dla kolorów
### CSS Variables
Wszystkie zmienne zdefiniowane w `_variables.scss`:
- `--color-accent`: #B50027
- `--color-bg-main`: #101010
- `--color-text-main`: #FFFFFF
- `--color-text-second`: #BEBEBE
- `--color-text-third`: #AFAFAF
- `--font-primary`: 'Inter', sans-serif
- `--font-secondary`: 'Protest Strike', sans-serif
- `--font-size-{element}-{breakpoint}`: rozmiary czcionek
### Breakpointy (domyślne Tailwind v4)
- sm: 640px
- md: 768px
- xl: 1280px
- 2xl: 1536px

## ACF Blocks Inventory
do uzupełnienia w trakcie realizacji zadań

## WordPress Configuration
do uzupełnienia w trakcie realizacji zadań

## Development Workflow
- Branch: master (project init)
- Commits: Conventional Commits
- Testing: Manual + Browser Stack

## plan pracy
** plan prac jest poglądowy, zadanie definiowane jest według punktu w plania **
1. bazowa struktura scss + js
2. bazowa struktura szablonów stron oraz definicja bloków ACF i odpowiadających im blade-ów
3. dostosowanie szablonu do projektu Figma