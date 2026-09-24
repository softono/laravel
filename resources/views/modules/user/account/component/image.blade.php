<x-ui.dialog-header>
    <x-ui.dialog-title>Change Avatar</x-ui.dialog-title>
</x-ui.dialog-header>
<div class="text-center">
    <div id="file-input-container">
        <input type="file" onchange="imageCrop.setCropFile(this.files[0])" class="hidden"
            accept="image/png, image/gif, image/jpeg, image/webp , image/jpg">
        <x-ui.button variant="secondary" onclick="$(this).prev().click()"><i class="bx bx-folder-open" aria-hidden="true"></i> Choose File</x-ui.button>
    </div>
    <div class="mx-auto my-5 max-w-full">
        <img src="{{ $general->getFileUrl(@$model->image, 'profile') }}" id="image-crop" class="max-w-full">
    </div>
    <div class="image-crop-action mt-3 flex justify-center gap-2" style="display:none;">
        <x-ui.button variant="outline" onclick="imageCrop.rotateLeft()">Rotate Left</x-ui.button>
        <x-ui.button variant="outline" onclick="imageCrop.rotateRight()">Rotate Right</x-ui.button>
    </div>
</div>
<x-ui.dialog-footer class="sm:justify-between">
    @if ($model->image)
        <x-ui.button variant="destructive" onclick="app.confirmAction(this);"
            data-action="{{ route($prefix.'account/delete-image') }}" data-id="{{ $model->image }}" data-next="reload">Delete Image</x-ui.button>
    @endif
    <x-ui.button variant="success" onclick="imageCrop.uploadImage();">Save</x-ui.button>
</x-ui.dialog-footer>
<script>
    var imageCrop = false;
    documentReady(function() {
        app.addCSS(['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css'])
        app.loadScript('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js', function() {
            imageCrop = new ImageCrop();
            imageCrop.init('image-crop', '{{ route($prefix.'account/image-save') }}', {next: 'reload'});
        });
    })
</script>
