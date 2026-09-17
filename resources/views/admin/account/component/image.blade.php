<div class="modal-header">
    <h4 class="modal-title">Change Avatar</h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

</div>
<div class="modal-body">
    <div class="col-md-12 text-center">
        <div id="file-input-container" method="POST">
            <input type='file' onchange="imageCrop.setCropFile(this.files[0])" style="display:none" accept="image/png, image/gif, image/jpeg, image/webp , image/jpg">
            <button type="button" class="btn btn-secondary file-btn fdgmage" onclick="$(this).prev().click()"><i class="mx-1 icon-base bx bx-upload" aria-hidden="true"> </i>Choose File</button>
        </div>
        <div class="image-crop-box" style="max-width:200%;">
            <img src="{{ $general->getFileUrl(@$model->image,'profile'); }}" id="image-crop">
        </div>
        <div class="image-crop-action" style="display:none;max-width:100%;">
            <button onclick="imageCrop.rotateLeft()" class="btn btn-default">Rotate Left</button>
            <button onclick="imageCrop.rotateRight()" class="btn btn-default">Rotate Right</button>
        </div>
    </div>
</div>
<div class="modal-footer justify-content-between">
    @if(!empty($model->image))
    <button type="button" class="btn btn-danger" onclick="app.confirmAction(this);" data-action="{{route('admin/account/delete-image')}}" data-id="{{$model->image}}">Delete Image</button>
    @endif
    <br>
    <button type="button" class="btn btn-success" class="submit" onclick="imageCrop.uploadImage();">Save</button>
</div>
<script>
    var imageCrop = false;
    documentReady(function() {
        app.addCSS(['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css'])
        app.loadScript('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js',function(){
            imageCrop = new ImageCrop();
            imageCrop.init('image-crop','admin/account/image-save');
        });
    })
</script>