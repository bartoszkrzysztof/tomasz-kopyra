@php
    $nav1 = [];
    $nav2 = [];

    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $nav1[] = [
            'text' => 'Zlecenia',
            'url' => ultrawet_get_dashboard_url(),
        ];
        if (ultrawet_user_can_edit()) {
            $nav1[] = [
                'text' => 'Użytkownicy',
                'url' => ultrawet_get_users_list_url(),
            ];
        }
        $nav2[] = [
            'text' => ultrawet_user_name() . ' <img src="' . Vite::asset('resources/images/settings.svg') . '" alt="Settings" class="inline h-5 w-5 mr-1">',
            'url' => ultrawet_get_account_url(),
        ];
        $nav2[] = [
            'text' => 'Wyloguj się',
            'url' => wp_logout_url(home_url()),
        ];
    } 
    else {
        $nav2[] = [
            'text' => 'Zaloguj się',
            'url' => ultrawet_get_login_url(),
        ];
        $nav2[] = [
            'text' => 'Zarejestruj się',
            'url' => ultrawet_get_register_url(),
        ];
    }
@endphp
<header class="flex justify-between items-center px-4 py-2 bg-gray-800 text-white">
    <nav>
        <ul class="flex space-x-2">
            @foreach($nav1 as $item)
                <li class="{!! isset($item['color']) ? $item['color'] : '' !!} px-3 py-2 rounded">
                    <a href="{{ $item['url'] }}" class="!no-underline opacity-100 hover:opacity-80 transition">
                        {!!  $item['text'] !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
    <nav>
        <ul class="flex space-x-2">
            @foreach($nav2 as $item)
                <li class="{!! isset($item['color']) ? $item['color'] : '' !!} px-3 py-2 rounded">
                    <a href="{{ $item['url'] }}" class="!no-underline opacity-100 hover:opacity-80 transition">
                        {!!  $item['text'] !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>