@php
    $blockWrapper = lotka_block_wrapper($block, [], 'div', []);
@endphp
{!! $blockWrapper['start'] !!}
    {!! $content !!}
{!! $blockWrapper['end'] !!}