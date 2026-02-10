{{--
  Masonry Gallery Component
  @props
    - ajaxAction: string - WordPress ajax action name
    - nonce: string - WordPress nonce for ajax security
--}}
<div class="masonry-gallery" data-gallery-container data-action="{{ $ajaxAction ?? '' }}" data-nonce="{{ $nonce ?? '' }}" data-ajax-url="{{ admin_url('admin-ajax.php') }}" data-has-more="{{ $hasMore ? '1' : '0' }}">
    <div class="masonry-grid" data-masonry-grid>
        <div class="masonry-grid-sizer" data-masonry-sizer></div>
        {{-- Elementy galerii --}}
        @foreach($items ?? [] as $item)
            @include('components.masonary-gallery-item', ['item' => $item])
        @endforeach
    </div>
  
    @if ($hasMore)
        <div class="gallery-actions">
            <button type="button" class="button-loading text-base" data-gallery-load-more>{!! __('pokaż więcej', 'tkopera') !!} <i data-lucide="arrow-down" class="w-4 h-4"></i></button>
        </div>
    @endif

</div>
