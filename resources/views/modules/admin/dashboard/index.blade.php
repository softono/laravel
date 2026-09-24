@extends('modules.admin.layouts.main')
@section('title')
    Dashboard
@endsection
@section('content')
    @php
        $cards = [
            ['label' => 'Total Users', 'value' => $counts['total'], 'icon' => 'bx-user', 'color' => 'text-chart-3'],
            ['label' => 'Active Users', 'value' => $counts['active'], 'icon' => 'bx-user-check', 'color' => 'text-success'],
            ['label' => 'Inactive Users', 'value' => $counts['inactive'], 'icon' => 'bx-user-x', 'color' => 'text-destructive'],
        ];
    @endphp

    <x-ui.page-header title="Dashboard" :crumbs="[['Dashboard']]" />

    <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $card)
            <x-ui.card class="gap-2 py-5">
                <x-ui.card-content class="flex items-center gap-4">
                    <div class="bg-muted flex size-12 items-center justify-center rounded">
                        <i class="bx {{ $card['icon'] }} {{ $card['color'] }} text-3xl"></i>
                    </div>
                    <div>
                        <h4 class="text-3xl font-bold">{{ number_format($card['value']) }}</h4>
                        <p class="text-muted-foreground text-sm">{{ $card['label'] }}</p>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endforeach
    </div>

    @include('modules.admin.dashboard.chart', ['counts' => $counts])
@endsection
