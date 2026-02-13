<div class="relative animation-gallery-block block-spacer -trippel !pb-0 {{ $section_classes }}">
    @if ($bg) 
        <div class="animation-bg js-animation-bg {{ $bg_position }}">
            {!! wp_get_attachment_image($bg, 'full', false, ['class' => 'animation-gallery-bg']) !!}
        </div>
    @endif
    <div class="container">
        @if ($header || $main_content)
            <div class="container-post mb-20 relative z-10">
                @if ($header)
                    <h2 class="text-center post-title -smaller font-secondary mb-8 animate-page-headline">{{ $header }}</h2>
                @endif
                @if ($main_content)
                    <div class="wysiwyg-content w-full md:w-[80%] md:mx-auto animate-page-headline">{!! $main_content !!}</div>
                @endif
            </div>
        @endif
        <div class="animation-gallery-wrapper">
            <div class="animation-gallery-empty-wrapper"></div>
            <div class="animation-gallery-items">
                <div class="masonry-gallery" data-gallery-container>
                    <div class="masonry-grid" data-masonry-grid>
                        <div class="masonry-grid-sizer" data-masonry-sizer></div>
                        {{-- Elementy galerii --}}
                        @foreach ($paints as $paint)
                            @include('components.masonary-gallery-item', ['item' => $paint])
                        @endforeach
                        @foreach ($paints as $paint)
                            @include('components.masonary-gallery-item', ['item' => $paint])
                        @endforeach
                        @foreach ($paints as $paint)
                            @include('components.masonary-gallery-item', ['item' => $paint])
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="animation-gallery-link-wrapper">
                <a href="{{ $link['url'] }}" class="gallery-link">
                    {{ $link['title'] }}
                    <i data-lucide="arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>