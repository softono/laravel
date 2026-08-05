<form method="post" action="{{ route('buckets/save') }}" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$bucket->id }}">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="name">Bucket Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" placeholder="my-bucket" name="name"
                    value="{{ @$bucket->name }}" {{ isset($bucket) ? 'readonly' : '' }} required
                    pattern="[a-z0-9]([a-z0-9.-]*[a-z0-9])?" minlength="3" maxlength="63" />
                <small class="text-muted">Lowercase letters, numbers, dots and hyphens only. Cannot be changed after creation.</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="visibility">Visibility <span class="text-danger">*</span></label>
                <select class="form-select" id="visibility" name="visibility" required>
                    <option value="private" {{ @$bucket->visibility == 'private' ? 'selected' : '' }}>Private</option>
                    <option value="public" {{ @$bucket->visibility == 'public' ? 'selected' : '' }}>Public (anonymous GET/HEAD allowed)</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label" for="storage_quota">Storage Quota (bytes, optional)</label>
                <input type="number" class="form-control" id="storage_quota" min="0" name="storage_quota"
                    value="{{ @$bucket->storage_quota }}" placeholder="Unlimited" />
                <small class="text-muted">Stored for reference only - not enforced in this version.</small>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="{{ route('buckets') }}" class="btn btn-outline-secondary pjax">Back</a>
</form>

@push('scripts')
<script>
    documentReady(function () {
        $('#ajax-form').validate({
            submitHandler: function (form) {
                app.ajaxForm(form);
            },
        });
    });
</script>
@endpush
