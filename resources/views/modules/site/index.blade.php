@extends('layouts.main')
@section('title')
    Home
@endsection
@section('content')
    @php
        $features = [
            ['icon' => 'bx-bolt-circle', 'title' => 'Lightning Fast', 'desc' => 'Optimized for speed with server-side rendering and smart caching out of the box.'],
            ['icon' => 'bx-shield-quarter', 'title' => 'Secure by Default', 'desc' => 'Built-in authentication, role-based access control, and data validation.'],
            ['icon' => 'bx-bar-chart-alt-2', 'title' => 'Admin Dashboard', 'desc' => 'A powerful admin panel to manage users, content, and settings with ease.'],
        ];
    @endphp
    <div class="min-h-screen">
        <section class="px-6 py-20 text-center sm:py-32 lg:px-10">
            <h1 class="mx-auto max-w-3xl text-4xl font-bold tracking-tight sm:text-6xl">
                Build something amazing, <span class="text-primary">faster</span>
            </h1>
            <p class="text-muted-foreground mx-auto mt-6 max-w-2xl text-lg sm:text-xl">
                A modern full-stack platform to power your next project. Simple, fast, and ready to scale.
            </p>
            <div class="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
                <x-ui.button :href="route('register')" size="lg">Get Started <i class="bx bx-right-arrow-alt"></i></x-ui.button>
                <x-ui.button :href="route('login')" size="lg" variant="outline">Sign In</x-ui.button>
            </div>
        </section>

        <section class="bg-muted/50 px-6 py-20 lg:px-10">
            <div class="mx-auto max-w-5xl">
                <h2 class="mb-12 text-center text-3xl font-bold">Everything you need</h2>
                <div class="grid gap-8 sm:grid-cols-3">
                    @foreach ($features as $feature)
                        <div class="bg-card text-card-foreground rounded-lg border p-6 shadow-sm">
                            <i class="bx {{ $feature['icon'] }} text-primary mb-4 text-4xl"></i>
                            <h3 class="mb-2 text-lg font-semibold">{{ $feature['title'] }}</h3>
                            <p class="text-muted-foreground text-sm">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="px-6 py-20 text-center lg:px-10">
            <h2 class="mb-4 text-3xl font-bold">Ready to get started?</h2>
            <p class="text-muted-foreground mx-auto mb-8 max-w-xl">Create your account in seconds and start building today.</p>
            <x-ui.button :href="route('register')" size="lg">Create Free Account <i class="bx bx-right-arrow-alt"></i></x-ui.button>
        </section>
    </div>
@endsection
