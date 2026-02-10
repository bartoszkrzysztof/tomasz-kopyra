<div class="masonry-item" data-masonry-item>
    <a href="{{ $item['link'] ?? '#' }}" class="masonry-item-inner">
        {!! wp_get_attachment_image($item['image_id'], 'gallery-thumb', false, ['class' => 'masonry-item-image']) !!}
        <div class="masonry-image-overlay">
            <p class="overlay-text">{!! __('poznaj szczegóły', 'tkopera') !!} <i data-lucide="arrow-right" class="w-5 h-5 pt-1"></i></p>
        </div>
    </a>
</div>