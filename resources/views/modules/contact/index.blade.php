@extends('layouts.main')
@section('title')
    Contact
@endsection
@section('content')
    <div class="w-full py-8 sm:px-6 lg:px-10">
        <h1 class="pb-4 text-xl font-bold tracking-tight sm:text-2xl">Contact</h1>
        <div class="bg-card border-border mt-4 w-full rounded-lg border p-4 shadow sm:mt-6 sm:p-6 md:p-8">
            <form id="contact-form" method="post" action="{{ route('contact-process') }}" class="space-y-5" data-next="refresh">
                @csrf
                <div class="space-y-2">
                    <x-ui.label for="name">Name <span class="text-red-500">*</span></x-ui.label>
                    <x-ui.input id="name" name="name" placeholder="Name" required />
                </div>
                <div class="space-y-2">
                    <x-ui.label for="email">Email <span class="text-red-500">*</span></x-ui.label>
                    <x-ui.input type="email" id="email" name="email" placeholder="Email" required />
                </div>
                <div class="space-y-2">
                    <x-ui.label for="subject">Subject <span class="text-red-500">*</span></x-ui.label>
                    <x-ui.input id="subject" name="subject" placeholder="Subject" required />
                </div>
                <div class="space-y-2">
                    <x-ui.label for="message">Message <span class="text-red-500">*</span></x-ui.label>
                    <x-ui.textarea id="message" name="message" placeholder="Message" rows="5" required />
                </div>
                @include('common.recaptcha')
                <x-ui.button type="submit">Submit</x-ui.button>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            $('#contact-form').validate({
                rules: {
                    name: {required: true, minlength: 2},
                    email: {required: true, email: true},
                    subject: {required: true, minlength: 5},
                    message: {required: true, minlength: 10},
                },
                submitHandler: function(form) {
                    app.ajaxForm(form);
                    try {
                        grecaptcha.reset();
                    } catch (e) {}
                },
            });
        });
    </script>
@endpush
