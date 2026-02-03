@if ($social_media_links)
    <ul class="social-media {{ $classes ?? '' }}">
        @foreach ($social_media_links as $link)
            <li class="social-media-item">
                <a href="{{ $link['link'] }}" target="_blank" rel="noopener noreferrer" class="social-media-link">
                    @if (!empty($link['custom_icon']))
                        {!! wp_get_attachment_image($link['custom_icon'], 'full', false, ['class' => 'social-media-image']) !!}
                    @elseif (!empty($link['icon']))
                        <i data-lucide="{{ $link['icon'] }}" class="social-media-icon"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
@endif