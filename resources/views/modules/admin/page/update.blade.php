@extends('modules.admin.layouts.main')
@section('title')
    Page Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Pages</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/page') }}" class="pjax hover:text-primary-600">Pages</a>
                </li>
                <li class="breadcrumb-item active">Page Update</li>
            </ol>
        </nav>
    </div>

    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Page Update</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content>
            <form id="ajax-form" method="post" action="{{ route('admin/page/save') }}" class="space-y-6"
                data-next="load" data-next-url="{{ route('admin/page') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $model->id }}">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <x-ui.label for="title">Title <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="title" name="title" :value="$model->title" required />
                    </div>
                    <div class="space-y-2">
                        <x-ui.label for="slug">Slug <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input id="slug" name="slug" :value="$model->slug" required />
                    </div>
                    <div class="space-y-2">
                        <x-ui.label for="status">Status</x-ui.label>
                        <x-ui.select id="status" name="status">
                            @foreach ([\App\Constants\UserStatus::ACTIVE => 'Active', \App\Constants\UserStatus::INACTIVE => 'Inactive'] as $value => $label)
                                <option value="{{ $value }}" @selected($model->status === $value)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div class="space-y-2">
                        <x-ui.label for="meta_title">Meta Title</x-ui.label>
                        <x-ui.input id="meta_title" name="meta_title" :value="$model->meta_title" />
                    </div>
                </div>
                <div class="space-y-2">
                    <x-ui.label for="meta_description">Meta Description</x-ui.label>
                    <x-ui.textarea id="meta_description" name="meta_description" rows="2">{{ $model->meta_description }}</x-ui.textarea>
                </div>
                <div class="space-y-2">
                    <x-ui.label for="body">Body <span class="text-destructive">*</span></x-ui.label>
                    <textarea name="body" id="body">{{ $model->body }}</textarea>
                </div>
                <div class="flex gap-2">
                    <x-ui.button type="submit">Submit</x-ui.button>
                    <x-ui.button :href="route('admin/page')" variant="outline" class="pjax">Back</x-ui.button>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>

    @include('modules.admin.partials.editor', ['selector' => '#body', 'uploadRoute' => 'admin/page/save-image'])
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
