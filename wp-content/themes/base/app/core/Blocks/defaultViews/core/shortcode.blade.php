@php
    $shortcode = strip_tags($block['innerHTML'] ?? '');
    $shortcode = trim(preg_replace('/\s+/', ' ', $shortcode));
@endphp

@if(is_admin())
    <div class="wp-block-shortcode">{{ $shortcode }}</div>
@else
    {!! do_shortcode($shortcode) !!}
@endif