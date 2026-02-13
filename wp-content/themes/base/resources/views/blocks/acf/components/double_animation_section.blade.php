@php
    $container = $content['container'] ?? false;
    $order = $order ?? 'left';
@endphp
@if ($container)
    <div class="half-container -{{ $order }} h-full flex flex-col justify-center">
@endif

@if ($content['image'])
    <div class="double-section-image">
        {!! wp_get_attachment_image($content['image'], 'full', false, ['class' => 'double-section-image-content']) !!}
    </div>
@endif

@if ($content['title'] || $content['text'])
    <div class="double-section-text mt-4 md:mt-8">
        @if ($content['text'])
            <div class="wysiwyg-content">{!! $content['text'] !!}</div>
        @endif
    </div>
@endif

@if ($container)
    </div>
@endif