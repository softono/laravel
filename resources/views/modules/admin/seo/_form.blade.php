<form id="ajax-form" method="post" action="{{ route('admin/seo/save') }}" class="space-y-6"
    data-next="load" data-next-url="{{ route('admin/seo/meta') }}">
    @csrf
    <input type="hidden" name="id" value="{{ $model->id ?? '' }}">
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-2">
            <x-ui.label for="url">URL (route name) <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input id="url" name="url" placeholder="home" :value="$model->url ?? ''" required />
        </div>
        <div class="space-y-2">
            <x-ui.label for="type">Type</x-ui.label>
            <x-ui.select id="type" name="type">
                @foreach (['STATIC', 'DYNAMIC'] as $type)
                    <option value="{{ $type }}" @selected(($model->type ?? 'STATIC') === $type)>{{ $type }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="title">Title <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input id="title" name="title" :value="$model->title ?? ''" required />
        </div>
        <div class="space-y-2">
            <x-ui.label for="keyword">Keywords</x-ui.label>
            <x-ui.input id="keyword" name="keyword" :value="$model->keyword ?? ''" />
        </div>
    </div>
    <div class="space-y-2">
        <x-ui.label for="description">Description</x-ui.label>
        <x-ui.textarea id="description" name="description" rows="3">{{ $model->description ?? '' }}</x-ui.textarea>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="space-y-2">
            <x-ui.label for="sitemap_enable">Sitemap</x-ui.label>
            <x-ui.select id="sitemap_enable" name="sitemap_enable">
                <option value="1" @selected(($model->sitemap_enable ?? 1) == 1)>Enabled</option>
                <option value="0" @selected(($model->sitemap_enable ?? 1) == 0)>Disabled</option>
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="change_frequency">Change frequency</x-ui.label>
            <x-ui.select id="change_frequency" name="change_frequency">
                <option value="">Select frequency</option>
                @foreach (\App\Modules\Admin\Seo\Requests\SaveSeoRequest::FREQUENCIES as $frequency)
                    <option value="{{ $frequency }}" @selected(($model->change_frequency ?? '') === $frequency)>{{ ucfirst($frequency) }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="priority">Priority (0 to 1)</x-ui.label>
            <x-ui.input type="number" step="0.1" min="0" max="1" id="priority" name="priority" :value="$model->priority ?? ''" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="last_modified">Last modified</x-ui.label>
            <x-ui.input id="last_modified" name="last_modified" placeholder="2026-01-31" :value="$model->last_modified ?? ''" />
        </div>
    </div>
    <div class="flex gap-2">
        <x-ui.button type="submit">Submit</x-ui.button>
        <x-ui.button :href="route('admin/seo/meta')" variant="outline" class="pjax">Back</x-ui.button>
    </div>
</form>
@push('scripts')
    <script>
        documentReady(function() {
            $('#ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                },
            });
        });
    </script>
@endpush
