@php 
    // dump($blocks);
@endphp

@if ($blocks)
    <div class="boxes-list container">
        @foreach ($blocks as $box)
            <div class="boxes-list__item">
                @include('components.block-component', ['block' => $box])
            </div>
        @endforeach
    </div>
@endif