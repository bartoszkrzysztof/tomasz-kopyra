<div class="animation-accordion-block relative block-spacer -trippel !pb-0">
    @if ($bg) 
        <div class="animation-bg js-animation-bg {{ $bg_position }}">
            {!! wp_get_attachment_image($bg, 'full', false, ['class' => 'animation-gallery-bg']) !!}
        </div>
    @endif
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

    @if ($panels && count($panels) > 0)
        <div class="container">
            <div class="accordion-wrapper js-accordion-wrapper">
                @foreach ($panels as $key => $panel)
                    @php
                        $bg_image_array = wp_get_attachment_image_src($panel['background'], 'full');
                        $background_image = $bg_image_array ? $bg_image_array[0] : '';
                    @endphp
                    <div class="accordion-item js-accordion-item" data-index="{{ $key }}">
                        <div class="accordion-background" style="background-image: url({{ $background_image }});">
                            <!-- Background placeholder - można dodać obraz przez ACF -->
                        </div>
                        <button class="accordion-trigger js-accordion-trigger" type="button" aria-label="Rozwiń panel">
                            <span class="sr-only">Kliknij aby rozwinąć</span>
                        </button>
                        <div class="accordion-content js-accordion-content">
                            @if ($panel['link']) 
                                <a href="{{ $panel['link']['url'] }}" class="accordion-content-inner -link">
                            @else 
                                <div class="accordion-content-inner">
                            @endif
                                <h3 class="accordion-title">{{ $panel['title'] }}</h3>
                                <div class="accordion-text wysiwyg-content !text-white">
                                    {!! $panel['text'] !!}
                                </div>
                            @if ($panel['link'])
                                </a>
                            @else
                                </div>
                            @endif
                        </div>
                    </div>
                
                @endforeach
            </div>
        </div>
    @endif
</div>