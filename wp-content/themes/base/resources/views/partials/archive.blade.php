<div class="archive-container">
    <header class="archive-header">
        <h1 class="archive-title">{{ $title }}</h1>
    </header>

    @if (have_posts())
        <div class="archive-posts">
            @while(have_posts())
                @php(the_post())
                <article @php(post_class('archive-item'))>
                    <h2 class="archive-item__title">
                        <a href="{{ get_permalink() }}">{{ get_the_title() }}</a>
                    </h2>
                    @if (has_post_thumbnail())
                        <div class="archive-item__thumbnail">
                            {{ the_post_thumbnail('medium') }}
                        </div>
                    @endif
                    <div class="archive-item__content">
                        @php(the_excerpt())
                    </div>
                </article>
            @endwhile
        </div>

        @if ($pagination())
            <nav class="archive-pagination">
                {!! $pagination !!}
            </nav>
        @endif
    @else
        <p class="archive-no-posts">Nie znaleziono postów.</p>
    @endif
</div>
