{{-- Sekcja 1: Animacja tła + wsunięcie tytułu z lewej --}}
<section class="animation-test-section" data-animation="section-1">
    <div class="section-bg" style="background-image:url(<?= Vite::asset('resources/images/temp/main.png') ?>);"></div>
    <div class="section-content">
        <div class="section-title-container">
            <h2 class="section-title font-secondary">Tomasz Alen Kopera</h2>
        </div>
    </div>
</section>

{{-- Sekcja 2: Pinned background + scroll masonry gallery --}}
<section class="animation-test-section" data-animation="section-2">
    <div class="section-bg" style="background-image:url(<?= Vite::asset('resources/images/temp/tlo1.png') ?>);"></div>
    <div class="section-content section2-wrapper">
        <div class="container">
            <div class="masonry-gallery">
                <div class="gallery-column">
                    @for ($i = 1; $i <= 4; $i++)
                    <div class="gallery-item">
                        <img src="<?= Vite::asset("resources/images/temp/obraz{$i}.jpg") ?>" alt="Gallery Image {{ $i }}">
                    </div>
                    @endfor    
                </div>
                <div class="gallery-column">
                    @for ($i = 3; $i <= 7; $i++)
                    <div class="gallery-item">
                        <img src="<?= Vite::asset("resources/images/temp/obraz{$i}.jpg") ?>" alt="Gallery Image {{ $i }}">
                    </div>
                    @endfor    
                </div>
                <div class="gallery-column">
                    @for ($i = 5; $i <= 8; $i++)
                    <div class="gallery-item">
                        <img src="<?= Vite::asset("resources/images/temp/obraz{$i}.jpg") ?>" alt="Gallery Image {{ $i }}">
                    </div>
                    @endfor    
                </div>
                <div class="gallery-column">
                    @for ($i = 1; $i <= 4; $i++)
                    <div class="gallery-item">
                        <img src="<?= Vite::asset("resources/images/temp/obraz{$i}.jpg") ?>" alt="Gallery Image {{ $i }}">
                    </div>
                    @endfor    
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Sekcja 3: Blur-to-sharp (left) + slide-in (right) --}}
<section class="animation-test-section" data-animation="section-3">
    <div class="pinned-section">
        <div class="split-box left-box">
            <div class="content">
                <img src="<?= Vite::asset("resources/images/temp/zegarek.png") ?>">
            </div>
        </div>
        <div class="split-box right-box">
            <div class="content">
                <img src="<?= Vite::asset("resources/images/temp/zegarek-right.jpg") ?>">
            </div>
        </div>
    </div>
</section>

{{-- Sekcja 4: 4 boxy slide-up + sekwencyjne highlight --}}
<section class="animation-test-section" data-animation="section-4">
    <div class="section-bg" style="background-image:url(<?= Vite::asset('resources/images/temp/tlo2.png') ?>);"></div>
    <div class="section-wrapper section4-wrapper">
        <div class="section-content">
            <div class="boxes-container">
                @for ($i = 1; $i <= 2; $i++)
                <div class="box-item">
                    <img src="<?= Vite::asset("resources/images/temp/frame{$i}.png") ?>">
                </div>
                @endfor
                @for ($i = 1; $i <= 2; $i++)
                <div class="box-item">
                    <img src="<?= Vite::asset("resources/images/temp/frame{$i}.png") ?>">
                </div>
                @endfor
            </div>
        </div>
    </div>
</section>

{{-- Sekcja 5: Opacity change (right) + slide-in (left) --}}
<section class="animation-test-section" data-animation="section-5">
    <div class="pinned-section">
        <div class="section-bg" style="background-image:url(<?= Vite::asset('resources/images/temp/tlo3.png') ?>);"></div>
        <div class="split-box left-box">
            <div class="content">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </div>
        </div>
        <div class="split-box right-box">
            <div class="content">
                <img src="<?= Vite::asset("resources/images/temp/tk.png") ?>">
            </div>
        </div>
    </div>
</section>

{{-- Sekcja 6: Pinned background + opacity + ruch od dołu --}}
<section class="animation-test-section" data-animation="section-6">
    <div class="section-bg" style="background-image:url(<?= Vite::asset('resources/images/temp/tlo4.png') ?>);"></div>
    <div class="pinned-section">
        <div class="split-box left-box">
            <div class="content">
                <img src="<?= Vite::asset("resources/images/temp/notes.png") ?>">
            </div>
        </div>
        <div class="split-box right-box">
            <div class="content">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
            </div>
        </div>
    </div>
</section>

<div class="section-footer">
    <div class="section-bg" style="background-image:url(<?= Vite::asset('resources/images/temp/tlo4.png') ?>);"></div>

</div>