@extends('layouts.main')
@section('title')
    Dashboard
@endsection
@section('content')
    @php
        $quickLinks = [
            ['icon' => 'bx-user', 'title' => 'My Profile', 'desc' => 'View and update your account details', 'href' => route('account/update')],
            ['icon' => 'bx-cog', 'title' => 'Settings', 'desc' => 'Manage your preferences and security', 'href' => route('account/two-factor')],
            ['icon' => 'bx-file', 'title' => 'Blog', 'desc' => 'Read the latest posts and articles', 'href' => url('blog')],
            ['icon' => 'bx-envelope', 'title' => 'Contact Us', 'desc' => 'Get in touch with our support team', 'href' => route('contact')],
        ];
    @endphp

    <div class="min-h-screen py-8 sm:px-6 lg:px-10">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Welcome back, {{ $user->first_name }}</h1>
            <p class="text-muted-foreground mt-1">Here's an overview of your account.</p>
        </div>

        <x-ui.card class="mb-8">
            <x-ui.card-header>
                <x-ui.card-title class="text-lg">Account Information</x-ui.card-title>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="grid gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <span class="text-muted-foreground">Name</span>
                        <p class="font-medium">{{ trim($user->first_name.' '.$user->last_name) ?: '—' }}</p>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Email</span>
                        <p class="font-medium">{{ $user->email }}</p>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Member since</span>
                        <p class="font-medium">{{ $general->dateFormat($user->created_at) }}</p>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <h2 class="mb-4 text-lg font-semibold">Quick Links</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($quickLinks as $item)
                <x-ui.card class="group transition-shadow hover:shadow-md">
                    <x-ui.card-content class="pt-6">
                        <i class="bx {{ $item['icon'] }} text-primary mb-3 text-3xl"></i>
                        <h3 class="mb-1 font-semibold">{{ $item['title'] }}</h3>
                        <p class="text-muted-foreground mb-4 text-sm">{{ $item['desc'] }}</p>
                        <x-ui.button :href="$item['href']" variant="ghost" size="sm" class="pjax px-0">
                            Go <i class="bx bx-right-arrow-alt ml-1"></i>
                        </x-ui.button>
                    </x-ui.card-content>
                </x-ui.card>
            @endforeach
        </div>
    </div>
@endsection
