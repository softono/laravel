@extends('modules.admin.layouts.main')
@section('title')
Profile
@endsection
@section('content')
<style>
  .swal2-container.swal2-center.swal2-shown {
    z-index: 9999;
  }
</style>
<div>
    <?= view('admin/account/component/account_block'); ?>
    <div class="card">
        <div class="card-body">
            <div class="flex flex-col items-start gap-6 border-b border-slate-200 pb-4 sm:flex-row sm:items-center">
                <img src="{{  $general->getFileUrl($model->image,'profile') }}" alt="user-avatar" class="block h-[100px] w-[100px] rounded" height="100px" width="100px" id="uploadedAvatar">
                <div>
                    <a onclick="app.showModalView('admin/account/image')" class="btn-primary pjax mb-4 inline-flex" tabindex="0">
                      <span class="hidden sm:block">Upload new photo</span>
                      <i class="bx bx-upload block sm:hidden"></i>
                    </a>
                    <div class="text-sm text-slate-600">Allowed JPG, GIF or PNG.</div>
                </div>
            </div>
      </div>
      <div class="card-body pt-4">
            <form action="admin/account/save" method="post" id="ajax-form">
              {{ csrf_field() }}
              <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                    <div>
                      <label for="first_name" class="form-label">First Name <span class="text-rose-600">*</span></label>
                      <input type="text" value="{{ $model->first_name }}" name="first_name" class="form-input" id="first_name" placeholder="Enter First Name" required="required" maxlength="128">
                    </div>

                    <div>
                      <label for="last_name" class="form-label">Last Name <span class="text-rose-600">*</span></label>
                      <input type="text" value="{{ $model->last_name }}" name="last_name" class="form-input" id="last_name" placeholder="Enter Last Name" required="required" maxlength="128">
                    </div>

                    <div>
                      <label for="email" class="form-label">E-mail <span class="text-rose-600">*</span></label>
                      <input type="email" value="{{ $model->email }}" name="email" class="form-input" id="email" placeholder="Enter Email" required="required">
                    </div>

                    <div>
                      <label class="form-label" for="phone">Phone Number <span class="text-rose-600">*</span></label>
                      <input type="number" value="{{ $model->phone }}" name="phone" class="form-input" id="phone" placeholder="Enter Phone Number" required="required" maxlength="10">
                    </div>

                 </div>

                  <div class="mt-6">
                    <button type="submit" class="btn-primary">Save changes</button>
                    <button type="reset" class="btn-dark">Cancel</button>
                  </div>

              </form>
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
