
<form method="post" action="admin/seo/save" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <div class="flex flex-wrap gap-4">
        <div class="w-full md:w-1/2">
            <div class="mb-3">
                <label class="form-label" for="url">URL (Without Domain Name) Ex.abc/def <span class="text-rose-500">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-input" id="url" placeholder="Enter URL Without Domain Name" name="url" value="{{ @$model->url }}" required />
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="title">Title <span class="text-rose-500">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-input" id="title" placeholder="Title" name="title" value="{{ @$model->title }}" required />
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description <span class="text-rose-500">*</span></label>
                    <textarea class="form-input" id="description" placeholder="Description" name="description" required>{{ @$model->description }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label" for="keyword">Keyword <span class="text-rose-500">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-input" id="keyword" placeholder="Keyword" name="keyword" value="{{ @$model->keyword }}" required />
                </div>
            </div>
        </div>

        <div class="w-full md:w-[calc(50%-1rem)]">
            <div class="mb-3">
            <small class="font-medium block">Site Map</small>
            <div class="inline-flex items-center gap-2 mt-4 mr-4">
                <input class="form-check-input" type="radio" name="site_map" id="frequency_enabled" value="1" x-data x-on:change="$refs.collapseFrequency.classList.toggle('hidden', false)" aria-expanded="false" aria-controls="collapseFrequency" <?= (@$model->sitemap_enable == 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="frequency_enabled">Enable</label>
            </div>
            <div class="inline-flex items-center gap-2">
                <input class="form-check-input" type="radio" name="site_map" id="frequency_disabled" value="0" x-on:change="$refs.collapseFrequency.classList.toggle('hidden', true)" aria-expanded="false" aria-controls="collapseFrequency" <?= (@$model->sitemap_enable == 0) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="frequency_disabled">Disable</label>
            </div>
            </div>
            <div class="<?= (@$model->sitemap_enable == 1) ? '' : 'hidden'; ?>" id="collapseFrequency" x-ref="collapseFrequency">
                <div class="mb-3">
                    <label class="form-label" for="frequency">Frequency</label>
                        <select class="form-select" name="frequency">
                        <option>Select Frequency</option>
                        <option value="weekly" <?php if (@$model->change_frequency === 'weekly') {
                                                    echo 'selected';
                                                } ?>>Weekly</option>
                        <option value="monthly" <?php if (@$model->change_frequency === 'monthly') {
                                                    echo 'selected';
                                                } ?>>Monthly</option>
                        <option value="yearly" <?php if (@$model->change_frequency === 'yearly') {
                                                    echo 'selected';
                                                } ?>>Yearly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="priority">Priority</label>
                    <input type="number" class="form-input" id="priority" placeholder="Priority" name="priority" value="{{ @$model->priority }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label" for="priority">Last Modified</label>
                        <input type="datetime" class="form-input" id="priority" placeholder="Priority" name="last_modified" value="{{ $general->currentTime() }}" />
                </div>
            </div>
        </div>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="btn-primary">Submit</button>
        <a href="admin/seo/meta" class="btn-dark pjax">Back</a>
    </div>
</form>

@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        jQuery.validator.addMethod("alphaOnly", function(value, element) {
            return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
        }, "Please enter only alphabetic characters");

        $('#ajax-form').validate({
            rules: {
                 url: {
                    required: true,
                },
                title: {
                    required: true,
                },
                description: {
                    required: true,
                },
                keyword: {
                    required: true,
                },

            },
            messages: {
                url: {
                    required: "Please enter the URL",
                },
                title: {
                    required: "Please enter the title",
                },
                description: {
                    required: "Please enter the description",
                },
                keyword: {
                    required: "Please enter the keyword"
                },
            },

            submitHandler: function(form) {
                app.ajaxFileForm(form);
            },
              errorPlacement: function(error, element) {
                error.insertAfter(element.closest('.mb-3'));
            },
        });

    });

</script>
@endpush