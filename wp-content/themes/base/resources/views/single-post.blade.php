@extends('layouts.app')

@section('content')
    @while(have_posts()) @php(the_post())
        <article @php(post_class('container-post'))>
            @include('components.post-header', ['title' => get_the_title()])
            @include('components.post-content', ['content' => get_the_content()])
        </article>
    @endwhile
@endsection