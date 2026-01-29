@php
    $blockWrapper = lotka_get_wrapper($attrs ?? [], ['container'], 'div');
@endphp
{!! $blockWrapper['start'] !!}
    {!! $content !!}
{!! $blockWrapper['end'] !!}