<div class="footer" id="js-main-footer">
    <div class="container">
        <div class="footer-content">
            @if ($footer_main_text)
                <div class="footer-content-title h2">{!! $footer_main_text !!}</div>
            @endif

            <div class="footer-content-left">
                @if ($footer_social_media_text)
                    <p class="footer-content-headline h5 mb-5 md:mb-10">{!! $footer_social_media_text !!}</p>
                @endif
                @if ($social_media_links)
                    @include('partials.social-media', ['social_media_links' => $social_media_links, 'classes' => 'mb-10'])
                @endif
                @if ($online_shop_link)
                    <div class="footer-content-left__shop">
                        @if ($footer_online_shop_text)
                            <p class="footer-content-headline mb-8">{!! $footer_online_shop_text !!}</p>
                        @endif
                        {!! $online_shop_link !!}
                    </div>
                @endif

                @if ($contact_email)
                    <div class="footer-contact-email">
                        <a href="mailto:{{ $contact_email }}" class="footer-contact-email__link">{!! $contact_email !!}</a>
                    </div>
                @endif
            </div>
            <div class="footer-content-right">
                @if ($footer_form_text)
                    <p class="footer-content-headline h5 mt-5 mb-0 md:mb-10 md:mt-0">{!! $footer_form_text !!}</p>
                @endif
                @if ($form_shortcode)
                    {!! do_shortcode($form_shortcode) !!}
                @endif

            </div>
            
            @if ($copyright_text)
                <div class="footer-copyright">{{ $copyright_text }}</div>
            @endif
        </div>
    </div>
</div>