@php
    $blockWrapper = lotka_block_wrapper($block, ['wrapper'], 'div', []);
@endphp
{!! $blockWrapper['start'] !!}
    {!! lotka_render_inner_blocks($block) !!}
{!! $blockWrapper['end'] !!}