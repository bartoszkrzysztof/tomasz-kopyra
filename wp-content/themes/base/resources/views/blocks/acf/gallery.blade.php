@if ($gallery)
    <div class="gallery-block block-spacer">
        {{-- Grid miniatur --}}
        <div class="gallery-grid">
            @foreach ($gallery as $index => $image_id)
                <button type="button" class="gallery-thumbnail" data-gallery-open="{{ $index }}" aria-label="{!! sprintf(__('Otwórz zdjęcie %d', 'tkopera'), $index + 1) !!}" data-target="#gp-{!! $block['id'] !!}">
                    {!! wp_get_attachment_image($image_id, 'large', false, ['class' => 'gallery-thumbnail__image', 'loading' => 'lazy']) !!}
                </button>
            @endforeach
        </div>
        
        <div class="gallery-popup" data-gallery-popup>
            <div class="gallery-popup__overlay" data-gallery-close></div>
            
            <div class="gallery-popup__container">
                <button type="button" class="gallery-popup__close" data-gallery-close aria-label="{!! __('Zamknij galerię', 'tkopera') !!}">
                    <i data-lucide="x" class="w-8 h-8"></i>
                </button>

                <div class="gallery-popup__main swiper" id="gp-{!! $block['id'] !!}">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $image_id)
                            @php
                                $image_full = wp_get_attachment_image_src($image_id, 'full');
                                $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                            @endphp
                            <div class="swiper-slide">
                                {!! wp_get_attachment_image($image_id, 'large', false, ['class' => 'gallery-popup__image']) !!}
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="gallery-popup__nav -prev">
                        <i data-lucide="arrow-left" class="w-4 h-4 md:w-8 md:h-8"></i>
                    </div>
                    <div class="gallery-popup__nav -next">
                        <i data-lucide="arrow-right" class="w-4 h-4 md:w-8 md:h-8"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif