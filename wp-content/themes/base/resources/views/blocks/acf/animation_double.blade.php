<div class="relative animation-double-block block-spacer -trippel !pb-0 {{ $section_classes }}" {!! $animation_settings !!}>
    @if ($bg) 
        <div class="animation-bg js-animation-bg {{ $bg_position }}">
            {!! wp_get_attachment_image($bg, 'full', false, ['class' => 'animation-gallery-bg']) !!}
        </div>
    @endif
    @if ($header || $main_content)
        <div class="container-post mb-20 relative z-10">
            @if ($header)
                <h2 class="text-center post-title -smaller font-secondary mb-8 animate-page-headline">{{ $header }}</h2>
            @endif
            @if ($main_content)
                <div class="wysiwyg-content w-full md:w-[80%] md:mx-auto animate-page-headline">{!! $main_content !!}</div>
            @endif
        </div>
    @endif
    <div class="animation-double-block-wrapper {{ $wrapper_classes }}">
        <div class="animation-double-block-left {{ $left_section_classes }}" {!! $left_section_settings !!}> 
            @include ('blocks.acf.components.double_animation_section', ['content' => $left_section, 'order' => 'left'])
        </div>
        <div class="animation-double-block-right {{ $right_section_classes }}" {!! $right_section_settings !!}>
            @include ('blocks.acf.components.double_animation_section', ['content' => $right_section, 'order' => 'right'])
        </div>
    </div>
</div>