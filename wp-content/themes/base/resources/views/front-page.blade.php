@extends('layouts.app')

@section('content')
    @while(have_posts()) @php(the_post())
        <article @php(post_class())>
            @include('components.post-content', ['content' => get_the_content()])
        </article>
    @endwhile
@endsection