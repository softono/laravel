@extends('admin.layouts.main')
@section('title')
Admin View
@endsection
@section('content')
<div class="breadcrumb-box">
  <h4 class="text-xl font-bold text-slate-800">Admin</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
      </li>
      <li class="breadcrumb-item">
        <a href="admin/admin" class="pjax hover:text-primary-600">Admin</a>
      </li>
      <li class="breadcrumb-item active">Admin View</li>
    </ol>
  </nav>
</div>

<!-- Content -->
<div class="flex flex-wrap gap-4">
  <!-- User Sidebar -->
  <div class="w-full lg:w-[calc(41.6667%-1rem)]">
    <!-- User Card -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="flex flex-col items-center">
          <img class="rounded mb-3 pt-1 mt-4" src="{{ $general->getFileUrl($model->image,'profile') }}" height="100" width="100" alt="User avatar" />
          <div class="text-center">
            <h4 class="mb-2 text-lg font-semibold text-slate-800">{{ $model->first_name.' '.$model->last_name }}</h4>
            @if ($model->type == 0)
            <span class="badge-soft-primary mt-1">SuperAdmin</span>
            @elseif ($model->type == 1)
            <span class="badge-soft-success mt-1">Admin</span>
            @elseif ($model->type == 2)
            <span class="badge-soft-secondary mt-1">User</span>
            @else
            <span class="badge-soft-secondary mt-1">Unknown</span>
            @endif
          </div>
        </div>
        <div class="mt-4">
          <ul class="list-none my-3 py-1 space-y-4">
            <li class="flex items-center">
              <i class="bx bx-user"></i>
              <span class="font-medium mx-2">Name:</span>
              <span>{{ $model->first_name.' '.$model->last_name }}</span>
            </li>
            <li class="flex items-start">
                <i class="bx bx-envelope"></i>
                <span class="font-medium mx-2">Email:</span>
                <span class="break-all">{{ $model->email }}</span>
            </li>
            <li class="flex items-center">
              <i class="bx bx-phone"></i>
              <span class="font-medium mx-2">Phone Number:</span>
              <span>{{ $model->phone }}</span>
            </li>
            <li class="flex items-center">
              <i class="bx bx-check"></i>
              <span class="font-medium mx-2">Status:</span>
              @if($model->status == 0)
              <span class="badge-soft-danger">Inactive</span>
              @else($model->status == 1)
              <span class="badge-soft-success">Active</span>
              @endif
            </li>
            <li class="flex items-center">
              <i class="bx bx-time"></i>
              <span class="font-medium mx-2">Created at:</span>
              <span>{{ $general->dateFormat($model->created_at) }}</span>
            </li>
            <li class="flex items-center">
              <i class="bx bx-time-five"></i>
              <span class="font-medium mx-2">Update at:</span>
                <span>{{ $general->dateFormat($model->updated_at) }}</span>
            </li>
            <li class="flex items-center">
              <i class="bx bx-timer"></i>
              <span class="font-medium mx-2">Time Zone:</span>
              <span>{{ $model->timezone }}</span>
            </li>
            <li class="flex items-center">
              <i class="bx bx-registered"></i>
              <span class="font-medium mx-2">Register Ip:</span>
              <span class="break-all">{{ $model->registered_ip }}</span>
            </li>
            <li class="flex items-center">
              <i class="bx bx-flag"></i>
              <span class="font-medium mx-2">Country:</span>
              <span>{{ $model->country }}</span>
            </li>
          </ul>
          <div class="flex justify-center gap-2">
            <a href="admin/admin/update?id={{$_GET['id']}}" class="btn-primary pjax">Edit</a>
            <button onclick="app.confirmAction(this);" data-action="admin/admin/delete?id={{$_GET['id']}}" class="btn-label-danger">Delete</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /User Card -->
    <!-- Plan Card -->

    <!-- /Plan Card -->
  </div>
  <!--/ User Sidebar -->

  <!-- User Content -->
  <div class="w-full lg:w-[calc(58.3333%-1rem)]">
    <!--/ User Pills -->

    <!-- Change Password -->
    <div class="card mb-4">
        <div class="flex justify-between items-center gap-3 px-5 pt-4">
            <h5 class="card-title mb-0">Recent Devices</h5>

             <a href="admin/device" class="btn-primary btn-sm pjax">
                        View All
            </a>
        </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm border-t border-slate-200 mt-3">
          <thead>
            <tr>
              <th class="truncate">Device</th>
              <th class="truncate">Location</th>
              <th class="truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            @foreach($deviceData as $device)
            <tr>
              <td class="truncate">{{ $general->deviceName($device->client) . ' ' . ($device->device_uid == @$_COOKIE[config("setting.app_uid").'_token'] ? ' (This Device)' : ''); }}</td>
              <td class="truncate">{{ $general->getIpLocation($device->ip);}}
                <p>({{$device->ip}})</p>
              </td>
              <td class="truncate">{{ date('Y-m-d h:i A', strtotime($device->created_at)); }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!--/ Change Password -->

    <!-- Two-steps verification -->

    <!--/ Two-steps verification -->

    <!-- Recent Devices -->
    <div class="card mb-4">
       <div class="flex justify-between items-center gap-3 px-5 pt-4">
            <h5 class="card-title mb-0">Activity</h5>

                <a href="admin/user-activity" class="btn-primary btn-sm pjax">
                View All
            </a>
         </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm border-t border-slate-200 mt-3">
          <thead>
            <tr>
              <th class="truncate">Type</th>
              <th class="truncate">Device</th>
              <th class="truncate">Location</th>
              <th class="truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logData as $log)
            <tr>
              <td class="truncate">
                @if($log->type==0)
                <strong>Login Fail</strong>
                @elseif($log->type==1)
                <strong>Login Success</strong>
                @elseif($log->type==2)
                <strong>Login By Remember</strong>
                @elseif($log->type==3)
                <strong>Register</strong>
                @elseif($log->type==4)
                <strong>Login With Otp</strong>
                @elseif($log->type==5)
                <strong>Login With Social Media</strong>
                @else
                <strong>Register With Social Media</strong>
                @endif
              </td>
              <td class="truncate">{{ $general->deviceName($log->client) }}</td>
              <td class="truncate">
                 {{-- {{ $general->getIpInfo($log->ip) }} --}}
                 <p>({{$log->ip}}) </p>
              </td>
              <td class="truncate">{{ date('Y-m-d h:i A', strtotime($log->created_at)); }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    <!--/ Recent Devices -->
  </div>
  <!--/ User Content -->
</div>

<!-- Modals -->
<!-- Edit User Modal (unused template boilerplate - light touch conversion) -->
<div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="editUser">
  <div class="modal-backdrop" data-modal-dismiss></div>
  <div class="modal-dialog relative z-10 w-full max-w-2xl">
    <div class="modal-content p-6">
      <div class="modal-body relative">
        <button type="button" class="btn-close absolute right-0 top-0" data-modal-dismiss aria-label="Close">
          <i class="bx bx-x text-xl"></i>
        </button>
        <div class="text-center mb-4">
          <h3 class="mb-2 text-lg font-semibold text-slate-800">Edit User Information</h3>
          <p class="text-slate-500">Updating user details will receive a privacy audit.</p>
        </div>
        <form id="editUserForm" class="grid grid-cols-1 sm:grid-cols-2 gap-4" onsubmit="return false">
          <div>
            <label class="form-label" for="modalEditUserFirstName">First Name</label>
            <input type="text" id="modalEditUserFirstName" name="modalEditUserFirstName" class="form-input" placeholder="John" />
          </div>
          <div>
            <label class="form-label" for="modalEditUserLastName">Last Name</label>
            <input type="text" id="modalEditUserLastName" name="modalEditUserLastName" class="form-input" placeholder="Doe" />
          </div>
          <div class="sm:col-span-2">
            <label class="form-label" for="modalEditUserName">Username</label>
            <input type="text" id="modalEditUserName" name="modalEditUserName" class="form-input" placeholder="john.doe.007" />
          </div>
          <div>
            <label class="form-label" for="modalEditUserEmail">Email</label>
            <input type="text" id="modalEditUserEmail" name="modalEditUserEmail" class="form-input" placeholder="example@domain.com" />
          </div>
          <div>
            <label class="form-label" for="modalEditUserStatus">Status</label>
            <select id="modalEditUserStatus" name="modalEditUserStatus" class="form-select" aria-label="Default select example">
              <option selected>Status</option>
              <option value="1">Active</option>
              <option value="2">Inactive</option>
              <option value="3">Suspended</option>
            </select>
          </div>
          <div>
            <label class="form-label" for="modalEditTaxID">Tax ID</label>
            <input type="text" id="modalEditTaxID" name="modalEditTaxID" class="form-input modal-edit-tax-id" placeholder="123 456 7890" />
          </div>
          <div>
            <label class="form-label" for="modalEditUserPhone">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text">US (+1)</span>
              <input type="text" id="modalEditUserPhone" name="modalEditUserPhone" class="form-input phone-number-mask" placeholder="202 555 0111" />
            </div>
          </div>
          <div>
            <label class="form-label" for="modalEditUserLanguage">Language</label>
            <select id="modalEditUserLanguage" name="modalEditUserLanguage" class="select2 form-select" multiple>
              <option value="">Select</option>
              <option value="english" selected>English</option>
              <option value="spanish">Spanish</option>
              <option value="french">French</option>
              <option value="german">German</option>
              <option value="dutch">Dutch</option>
              <option value="hebrew">Hebrew</option>
              <option value="sanskrit">Sanskrit</option>
              <option value="hindi">Hindi</option>
            </select>
          </div>
          <div>
            <label class="form-label" for="modalEditUserCountry">Country</label>
            <select id="modalEditUserCountry" name="modalEditUserCountry" class="select2 form-select" data-allow-clear="true">
              <option value="">Select</option>
              <option value="Australia">Australia</option>
              <option value="Bangladesh">Bangladesh</option>
              <option value="Belarus">Belarus</option>
              <option value="Brazil">Brazil</option>
              <option value="Canada">Canada</option>
              <option value="China">China</option>
              <option value="France">France</option>
              <option value="Germany">Germany</option>
              <option value="India">India</option>
              <option value="Indonesia">Indonesia</option>
              <option value="Israel">Israel</option>
              <option value="Italy">Italy</option>
              <option value="Japan">Japan</option>
              <option value="Korea">Korea, Republic of</option>
              <option value="Mexico">Mexico</option>
              <option value="Philippines">Philippines</option>
              <option value="Russia">Russian Federation</option>
              <option value="South Africa">South Africa</option>
              <option value="Thailand">Thailand</option>
              <option value="Turkey">Turkey</option>
              <option value="Ukraine">Ukraine</option>
              <option value="United Arab Emirates">United Arab Emirates</option>
              <option value="United Kingdom">United Kingdom</option>
              <option value="United States">United States</option>
            </select>
          </div>
          <div class="sm:col-span-2">
            <label class="switch inline-flex items-center gap-2">
              <input type="checkbox" class="switch-input" />
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label">Use as a billing address?</span>
            </label>
          </div>
          <div class="sm:col-span-2 text-center flex justify-center gap-2">
            <button type="submit" class="btn-primary">Submit</button>
            <button type="reset" class="btn-label-secondary" data-modal-dismiss aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->

<!-- Enable OTP Modal (unused template boilerplate - light touch conversion) -->
<div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="enableOTP">
  <div class="modal-backdrop" data-modal-dismiss></div>
  <div class="modal-dialog relative z-10 w-full max-w-md">
    <div class="modal-content p-6">
      <div class="modal-body relative">
        <button type="button" class="btn-close absolute right-0 top-0" data-modal-dismiss aria-label="Close">
          <i class="bx bx-x text-xl"></i>
        </button>
        <div class="text-center mb-4">
          <h3 class="mb-2 text-lg font-semibold text-slate-800">Enable One Time Password</h3>
          <p>Verify Your Mobile Number for SMS</p>
        </div>
        <p>Enter your mobile phone number with country code and we will send you a verification code.</p>
        <form id="enableOTPForm" class="grid grid-cols-1 gap-4 mt-3" onsubmit="return false">
          <div>
            <label class="form-label" for="modalEnableOTPPhone">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text">US (+1)</span>
              <input type="text" id="modalEnableOTPPhone" name="modalEnableOTPPhone" class="form-input phone-number-otp-mask" placeholder="202 555 0111" />
            </div>
          </div>
          <div class="flex gap-2">
            <button type="submit" class="btn-primary">Submit</button>
            <button type="reset" class="btn-label-secondary" data-modal-dismiss aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Enable OTP Modal -->

<!-- Add New Credit Card Modal (unused template boilerplate - light touch conversion) -->
<div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="upgradePlanModal">
  <div class="modal-backdrop" data-modal-dismiss></div>
  <div class="modal-dialog relative z-10 w-full max-w-lg">
    <div class="modal-content p-6">
      <div class="modal-body relative">
        <button type="button" class="btn-close absolute right-0 top-0" data-modal-dismiss aria-label="Close">
          <i class="bx bx-x text-xl"></i>
        </button>
        <div class="text-center mb-4">
          <h3 class="mb-2 text-lg font-semibold text-slate-800">Upgrade Plan</h3>
          <p>Choose the best plan for user.</p>
        </div>
        <form id="upgradePlanForm" class="grid grid-cols-1 sm:grid-cols-12 gap-4" onsubmit="return false">
          <div class="sm:col-span-8">
            <label class="form-label" for="choosePlan">Choose Plan</label>
            <select id="choosePlan" name="choosePlan" class="form-select" aria-label="Choose Plan">
              <option selected>Choose Plan</option>
              <option value="standard">Standard - $99/month</option>
              <option value="exclusive">Exclusive - $249/month</option>
              <option value="Enterprise">Enterprise - $499/month</option>
            </select>
          </div>
          <div class="sm:col-span-4 flex items-end">
            <button type="submit" class="btn-primary">Upgrade</button>
          </div>
        </form>
      </div>
      <hr class="border-slate-200" />
      <div class="modal-body">
        <p class="mb-0">User current plan is standard plan</p>
        <div class="flex justify-between items-center flex-wrap gap-2">
          <div class="flex justify-center items-center">
            <sup class="text-base pt-1 mt-3 mb-0 me-1 text-primary-600">$</sup>
            <h1 class="text-4xl mb-0 text-primary-600">99</h1>
            <sub class="text-sm mt-auto mb-2 text-slate-500">/month</sub>
          </div>
          <button class="btn-label-danger cancel-subscription mt-3">Cancel Subscription</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Credit Card Modal -->

@endsection
