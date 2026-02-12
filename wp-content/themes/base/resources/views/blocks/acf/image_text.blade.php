<div class="text-image-block block-spacer {{ $wrapper_classes }}">
    <div class="text-image-block__image-container {{ $image_classes }}">
        {!! wp_get_attachment_image($image, 'full', false, ['class' => 'text-image-block__image']) !!}
    </div>
    <div class="text-image-block__text-container wysiwyg-content -paragraphed {{ $text_classes }}">
        {!! apply_filters('the_content', $text) !!}
    </div>
</div>