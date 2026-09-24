<form id="ajax-form" method="post" action="{{ route('admin/blog/save') }}" enctype="multipart/form-data" class="space-y-6"
    data-next="load" data-next-url="{{ route('admin/blog') }}">
    @csrf
    <input type="hidden" name="id" value="{{ $model->id ?? '' }}">
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-2">
            <x-ui.label for="title">Title <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input id="title" name="title" :value="$model->title ?? ''" required />
        </div>
        <div class="space-y-2">
            <x-ui.label for="slug">Slug <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input id="slug" name="slug" placeholder="my-first-post" :value="$model->slug ?? ''" required />
        </div>
        <div class="space-y-2">
            <x-ui.label for="category">Category <span class="text-destructive">*</span></x-ui.label>
            <x-ui.select id="category" name="category" required>
                <option value="">Select category</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected(($model->category ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="status">Status</x-ui.label>
            <x-ui.select id="status" name="status">
                @foreach ([\App\Constants\UserStatus::ACTIVE => 'Active', \App\Constants\UserStatus::INACTIVE => 'Inactive'] as $value => $label)
                    <option value="{{ $value }}" @selected(($model->status ?? \App\Constants\UserStatus::ACTIVE) === $value)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="meta_title">Meta Title</x-ui.label>
            <x-ui.input id="meta_title" name="meta_title" :value="$model->meta_title ?? ''" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="image">Image</x-ui.label>
            <div class="flex items-center gap-3">
                @if (! empty($model->image))
                    <img src="{{ $general->getFileUrl($model->image, 'blog') }}" class="size-12 rounded-md border object-cover" id="image-preview" alt="">
                @else
                    <img src="{{ $general->getNoFile() }}" class="size-12 rounded-md border object-cover" id="image-preview" alt="">
                @endif
                <x-ui.input type="file" accept="image/*" name="image" onchange="previewImage(this,'#image-preview')" />
            </div>
        </div>
    </div>
    <div class="space-y-2">
        <x-ui.label for="meta_description">Meta Description</x-ui.label>
        <x-ui.textarea id="meta_description" name="meta_description" rows="2">{{ $model->meta_description ?? '' }}</x-ui.textarea>
    </div>
    <div class="space-y-2">
        <x-ui.label for="excerpt">Excerpt <span class="text-destructive">*</span></x-ui.label>
        <x-ui.textarea id="excerpt" name="excerpt" rows="3" required>{{ $model->excerpt ?? '' }}</x-ui.textarea>
    </div>
    <div class="space-y-2">
        <x-ui.label for="body">Content <span class="text-destructive">*</span></x-ui.label>
        <textarea name="body" id="body">{{ $model->body ?? '' }}</textarea>
    </div>
    <div class="flex gap-2">
        <x-ui.button type="submit">Submit</x-ui.button>
        <x-ui.button :href="route('admin/blog')" variant="outline" class="pjax">Back</x-ui.button>
    </div>
</form>

@include('modules.admin.partials.editor', ['selector' => '#body', 'uploadRoute' => 'admin/blog/save-image'])
@push('scripts')
    <script>
        documentReady(function() {
            $('#ajax-form').validate({
                ignore: [],
                submitHandler: function(form) {
                    $('#body').val($('#body').summernote('code'));
                    app.ajaxFileForm(form);
                },
            });
        });
    </script>
@endpush
