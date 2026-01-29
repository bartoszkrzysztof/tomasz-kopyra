@extends('layouts.app')

@section('content')
    <header>
        <h1>{{ $title }}</h1>
    </header>
    
    @if (have_posts())
        <div class="posts-list">
            @while(have_posts())
                @php(the_post())
                <article @php(post_class())>
                    <h2>
                        <a href="{{ get_permalink() }}">{{ get_the_title() }}</a>
                    </h2>
                    <div class="content">
                        @php(the_content())
                    </div>
                </article>
            @endwhile
        </div>
    @endif
@endsection
