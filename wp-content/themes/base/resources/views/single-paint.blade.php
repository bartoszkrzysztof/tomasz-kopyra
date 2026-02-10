@extends('layouts.app')

@section('content')
    <div class="container">
        @if ($gallery)
            <div id="single-paint-slider" class="single-slider">
                <div class="swiper single-slider-main">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $image_id)
                            <div class="swiper-slide">
                                {!! wp_get_attachment_image($image_id, 'gallery-thumb', false, ['class' => 'single-slider-image']) !!}
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="single-slider-controls">
                    <button class="single-slider-prev" aria-label="{!! __('Poprzedni obraz', 'tkopera') !!}"><i data-lucide="arrow-left" class="w-5 h-5"></i></button>
                    <button class="single-slider-next" aria-label="{!! __('Następny obraz', 'tkopera') !!}"><i data-lucide="arrow-right" class="w-5 h-5"></i></button>
                </div>
                <div class="swiper single-slider-thumbnails">
                    <div class="swiper-wrapper">
                        @foreach ($gallery as $image_id)
                            <div class="swiper-slide">
                                {!! wp_get_attachment_image($image_id, 'thumbnail', false, ['class' => 'single-slider-thumbnail-image']) !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <div class="wysiwyg-content w-full md:w-3/4 mx-auto">
            {!! apply_filters('the_content', $text) !!}
        </div>
        <div class="text-center mt-12">
            <a href="{{ $backLink }}" class="button-framed mx-auto"><i data-lucide="arrow-left" class="w-4 h-4"></i>{!! __('powrót do listy', 'tkopera') !!}</a>
        </div>
    </div>
@endsection
