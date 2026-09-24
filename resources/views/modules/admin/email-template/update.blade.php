@extends('modules.admin.layouts.main')
@section('title')
    Email Template Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Email Templates</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/email-template') }}" class="pjax hover:text-primary-600">Email Templates</a>
                </li>
                <li class="breadcrumb-item active">Update</li>
            </ol>
        </nav>
    </div>

    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">{{ $model->title }}</x-ui.card-title>
            @if ($model->params)
                <x-ui.card-description>
                    Placeholders: @foreach (explode(',', $model->params) as $param)<code class="bg-muted rounded px-1">&#123;&#123;{{ trim($param) }}&#125;&#125;</code> @endforeach
                </x-ui.card-description>
            @endif
        </x-ui.card-header>
        <x-ui.card-content>
            <form id="ajax-form" method="post" action="{{ route('admin/email-template/save') }}" class="space-y-6"
                data-next="load" data-next-url="{{ route('admin/email-template') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $model->id }}">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-ui.label for="title">Title <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="title" name="title" :value="$model->title" required />
                    </div>
                    <div class="space-y-2">
                        <x-ui.label for="subject">Subject <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="subject" name="subject" :value="$model->subject" required />
                    </div>
                </div>
                <div class="space-y-2">
                    <x-ui.label for="body">Body <span class="text-destructive">*</span></x-ui.label>
                    <textarea name="body" id="body">{{ $model->body }}</textarea>
                </div>
                <div class="flex gap-2">
                    <x-ui.button type="submit">Submit</x-ui.button>
                    <x-ui.button :href="route('admin/email-template')" variant="outline" class="pjax">Back</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>

    @include('modules.admin.partials.editor', ['selector' => '#body', 'uploadRoute' => 'admin/email-template/save-image'])
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            $('#ajax-form').validate({
                ignore: [],
                submitHandler: function(form) {
                    $('#body').val($('#body').summernote('code'));
                    app.ajaxForm(form);
                },
            });
        });
    </script>
@endpush
