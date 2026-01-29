@extends('layouts.app')

@section('content')
    @while(have_posts()) @php(the_post())
        <article @php(post_class())>
            <header>
                <h1>{{ $title }}</h1>
            </header>
            <div class="content">
                @php(the_content())
            </div>
        </article>
    @endwhile
@endsection
