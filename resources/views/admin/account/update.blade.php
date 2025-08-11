@extends('admin.layouts.main')
@section('title')
Profile
@endsection
@section('content')
<style>
  .swal2-container.swal2-center.swal2-shown {
    z-index: 9999;
  }
 </style>

<div class="row">
  <div class="col-md-12">
    <?= view('admin/account/component/account_block'); ?>
    <div class="card mb-6">
      <div class="card-body">
          <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
            <img src="{{  $general->getFileUrl($model->image,'profile') }}" alt="user-avatar" class="d-block w-px-100 h-px-100 rounded" alt="image" height="100px" width="100px" id="uploadedAvatar">
            <div class="button-wrapper">
                <a onclick="app.showModalView('admin/account/image')" for="upload" class="btn btn-primary me-3 mb-4 text-white pjax" tabindex="0">
                  <span class="d-none d-sm-block">Upload new photo</span>
                  <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                </a>
                <div>Allowed JPG, GIF or PNG.</div>
            </div>
          </div>
      </div>
      <div class="card-body pt-4">
            <form action="admin/account/save" method="post" id="ajax-form">
              {{ csrf_field() }}
              <div class="row g-6">

                    <div class="col-md-6">
                      <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                      <input type="text" value="{{ $model->first_name }}" name="first_name" class="form-control" id="first_name" placeholder="Enter First Name" required="required" maxlength="128">
                    </div>
                    
                    <div class="col-md-6">
                      <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                      <input type="text" value="{{ $model->last_name }}" name="last_name" class="form-control" id="last_name" placeholder="Enter Last Name" required="required" maxlength="128">
                    </div>

                    <div class="col-md-6">
                      <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                      <input type="email" value="{{ $model->email }}" name="email" class="form-control" id="email" placeholder="Enter Email" required="required">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label" for="phoneNumber">Phone Number <span class="text-danger">*</span></label>
                      <input type="number" value="{{ $model->phone }}" name="phone" class="form-control" id="phone" placeholder="Enter Phone Number" required="required" maxlength="10">
                    </div>

                 </div>

                  <div class="mt-6">
                    <button type="submit" class="btn btn-primary me-3">Save changes</button>
                    <button type="reset" class="btn btn-dark">Cancel</button>
                  </div>            
              
              </form>
          </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
  jQuery.validator.addMethod("noDisposableEmail", v => !["mailinator.com","tempmail.com","10minutemail.com","guerrillamail.com","fakeinbox.com"].includes((v.split('@')[1]||"").toLowerCase()), "Disposable email addresses are not allowed.");

  documentReady(function() {
    $('#ajax-form').validate({
      rules:{
            first_name: {
                    required: true,
                },
             last_name: {
                    required: true,
                },
            phone: {
                    required: true,
                    minlength: 10,
                },
         email: {
                    required: true,
                    email: true,
                    noDisposableEmail: true
                },
      },
      messages:{
              first_name: {
                       required: "Please enter the first name",
                },
                 last_name: {
                       required: "Please enter the last name",
                },
            email: {
                    required: "Please enter the email",
                    email: "Please enter a valid email address",
                    noDisposableEmail: "Please enter a valid Email domain"
                },
             phone: {
                       required: "Please enter the phone number",
                },
      },
      submitHandler: function(form) {
        app.ajaxForm(form);
      }
    })
  });
</script>
@endpush