<div class="modal-header">
    <h4 class="modal-title">Change Avatar</h4>
    <button type="button" class="btn-close" data-modal-dismiss aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="text-center">
        <div id="file-input-container" method="POST">
            <input type='file' onchange="imageCrop.setCropFile(this.files[0])" style="display:none"
                accept="image/png, image/gif, image/jpeg, image/webp , image/jpg">
            <button type="button" class="btn-secondary file-btn" onclick="$(this).prev().click()"><i
                    class="bx bx-folder-open mr-1" aria-hidden="true"></i> Choose File</button>
        </div>
        <div class="image-crop-box" style="max-width:200%;">
            <img src="{{ $general->getFileUrl(@$model->image, 'profile') }}" id="image-crop">
        </div>
        <div class="image-crop-action mt-3 flex justify-center gap-2" style="display:none;max-width:100%;">
            <button onclick="imageCrop.rotateLeft()" class="btn-outline">Rotate Left</button>
            <button onclick="imageCrop.rotateRight()" class="btn-outline">Rotate Right</button>
        </div>
    </div>
</div>
<div class="modal-footer justify-between">
    @if ($model->image)
        <button type="button" class="btn-danger" onclick="app.confirmAction(this);"
            data-action="{{ route($prefix.'account/delete-image') }}" data-id="{{ $model->image }}">Delete Image</button>
    @endif
    <button type="button" class="btn-success" onclick="imageCrop.uploadImage();">Save</button>
</div>
<script>
    var imageCrop = false;
    documentReady(function() {
        app.addCSS(['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css'])
        app.loadScript('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js', function() {
            imageCrop = new ImageCrop();
            imageCrop.init('image-crop', '{{ route($prefix.'account/image-save') }}');
        });
    })
</script>
