@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="post-title mb-8 font-secondary">{{ $title }}</h1>
        <div class="wysiwyg-content page-headline">
            {!! apply_filters('the_content', $text) !!}
        </div>
    
        <div class="gallery-section mt-20">    
            @include('components.masonary-gallery', ['items' => $items ?? []])
        </div>
        
    </div>
@endsection
