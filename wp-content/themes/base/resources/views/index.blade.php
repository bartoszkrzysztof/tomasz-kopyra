@extends('layouts.app')

@section('content')
    @if (is_archive())
        @php($post_type = get_queried_object()->taxonomy ?? 'post')
        @includeFirst(['partials.archive-' . $post_type, 'partials.archive'])
    @else 
      @while(have_posts()) @php(the_post())
        @includeFirst(['partials.content-single-' . get_post_type(), 'partials.content-single', 'partials.content-page', 'partials.content'])
      @endwhile
  @endif
@endsection