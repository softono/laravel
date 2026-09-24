@php
    // Flash messages: session key => [x-ui.alert variant, icon]
    $flash = [
        'success' => ['success', 'bx-check-circle'],
        'error' => ['destructive', 'bx-error-circle'],
        'warning' => ['warning', 'bx-error'],
        'info' => ['default', 'bx-info-circle'],
    ];
@endphp
@foreach ($flash as $key => [$variant, $icon])
    @if ($message = Session::get($key))
        <x-ui.alert :variant="$variant" class="mb-4">
            <x-ui.alert-description><i class="bx {{ $icon }} mr-1 align-middle text-base"></i>{!! $message !!}</x-ui.alert-description>
            <button type="button" data-alert-dismiss aria-label="Close" class="absolute top-2.5 right-3 cursor-pointer opacity-70 hover:opacity-100"><i class="bx bx-x text-lg"></i></button>
        </x-ui.alert>
    @endif
@endforeach
@if ($errors->any())
    <x-ui.alert variant="destructive" class="mb-4">
        <x-ui.alert-description><i class="bx bx-error-circle mr-1 align-middle text-base"></i>{!! $errors->first() !!}</x-ui.alert-description>
        <button type="button" data-alert-dismiss aria-label="Close" class="absolute top-2.5 right-3 cursor-pointer opacity-70 hover:opacity-100"><i class="bx bx-x text-lg"></i></button>
    </x-ui.alert>
@endif
