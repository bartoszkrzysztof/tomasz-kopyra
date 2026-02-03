<div class="main-header" id="js-main-header">
    <div class="main-header-nav main-nav-toggler js-nav-toggler" data-target="#js-main-nav"></div>
    <a href="{{ esc_url(home_url('/')) }}" class="main-header-title font-secondary">Tomasz Alen Kopera</a>
    <div class="main-header-nav -lang">
        <div class="lang-nav-toggler js-nav-toggler" data-target="#js-lang-nav">
            <span class="target">PL</span>
            <ul class="lang-nav" id="js-lang-nav">
                <li class="lang-nav-item"><a href="#">EN</a></li>
                <li class="lang-nav-item"><a href="#">PL</a></li>
                <li class="lang-nav-item"><a href="#">DE</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="main-nav" id="js-main-nav">
    @php
        wp_nav_menu([
            'theme_location' => 'primary',
            'menu_class'     => 'main-nav-list',
            'container'      => false,
        ]);
    @endphp
</div>