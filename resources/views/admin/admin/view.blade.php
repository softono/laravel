@extends('admin.layouts.main')
@section('title')
Admin View
@endsection
@section('content')
<style>
  .btn-label-danger{
    display: unset !important;
  }
</style>
<div class="breadcrumb-box">
  <h4 class="fw-bold py-3 mb-4">Admin</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax">Dashboard</a>
      </li>
      <li class="breadcrumb-item">
        <a href="admin/admin" class="pjax">Admin</a>
      </li>
      <li class="breadcrumb-item active">Admin View</li>
    </ol>
  </nav>
</div>

<!-- Content -->
<div class="row">
  <!-- User Sidebar -->
  <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
    <!-- User Card -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="user-avatar-section">
          <div class="d-flex align-items-center flex-column">
            <img class="img-fluid rounded mb-3 pt-1 mt-4" src="{{ $general->getFileUrl($model->image,'profile') }}" height="100" width="100" alt="User avatar" />
            <div class="user-info text-center">
              <h4 class="mb-2">{{ $model->first_name.' '.$model->last_name }}</h4>
              @if ($model->type == 0)
              <span class="badge bg-label-primary mt-1">SuperAdmin</span>
              @elseif ($model->type == 1)
              <span class="badge bg-label-success mt-1">Admin</span>
              @elseif ($model->type == 2)
              <span class="badge bg-label-dark mt-1">User</span>
              @else
              <span class="badge bg-label-default mt-1">Unknown</span>
              @endif
            </div>
          </div>
        </div>
        <br>
        <div class="info-container">
          <ul class="list-unstyled my-3 py-1">
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-user"></i>
              <span class="fw-medium mx-2">Name:</span>
              <span>{{ $model->first_name.' '.$model->last_name }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
                <i class="icon-base bx bx-envelope"></i>
                <span class="fw-medium mx-2">Email:</span>
                <span class="text-break">{{ $model->email }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-phone"></i>
              <span class="fw-medium mx-2">Phone Number:</span>
              <span>{{ $model->phone }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-check"></i>
              <span class="fw-medium mx-2">Status:</span>
              @if($model->status == 0)
              <span class="badge rounded-pill bg-label-danger">Inactive</span>
              @else($model->status == 1)
              <span class="badge rounded-pill bg-label-success">Active</span>
              @endif
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-time"></i>
              <span class="fw-medium mx-2">Created at:</span>
              <span>{{ $general->dateFormat($model->created_at) }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-time-five"></i>
              <span class="fw-medium mx-2">Update at:</span>
                <span>{{ $general->dateFormat($model->updated_at) }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-timer"></i>
              <span class="fw-medium mx-2">Time Zone:</span>
              <span>{{ $model->timezone }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-registered"></i>
              <span class="fw-medium mx-2">Register Ip:</span>
              <span class="text-break">{{ $model->registered_ip }}</span>
            </li>
            <li class="d-flex align-items-center mb-4">
              <i class="icon-base bx bx-flag"></i>
              <span class="fw-medium mx-2">Country:</span>
              <span>{{ $model->country }}</span>
            </li>
          </ul>
          <div class="d-flex justify-content-center">
            <a href="admin/admin/update?id={{$_GET['id']}}" class="btn btn-primary me-3 pjax">Edit</a>
            <button onclick="app.confirmAction(this);" data-action="admin/admin/delete?id={{$_GET['id']}}" class="btn btn-label-danger">Delete</button>
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
  <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
    <!--/ User Pills -->

    <!-- Change Password -->
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-header mb-0">Recent Devices</h5>

             <a href="admin/device" class="btn btn-sm btn-primary me-3 pjax">
                        View All
            </a>
        </div>
      <div class="table-responsive">
        <table class="table border-top">
          <thead>
            <tr>
              <th class="text-truncate">Device</th>
              <th class="text-truncate">Location</th>
              <th class="text-truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            @foreach($deviceData as $device)
            <tr>
              <td class="text-truncate">{{ $general->deviceName($device->client) . ' ' . ($device->device_uid == @$_COOKIE[config("setting.app_uid").'_token'] ? ' (This Device)' : ''); }}</td>
              <td class="text-truncate">{{ $general->getIpLocation($device->ip);}}
                <p>({{$device->ip}})</p>
              </td>
              <td class="text-truncate">{{ date('Y-m-d h:i A', strtotime($device->created_at)); }}</td>
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
       <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-header mb-0">Activity</h5>

                <a href="admin/user-activity" class="btn btn-sm btn-primary me-3 pjax">
                View All
            </a>
         </div>
      <div class="table-responsive">
        <table class="table border-top">
          <thead>
            <tr>
              <th class="text-truncate">Type</th>
              <th class="text-truncate">Device</th>
              <th class="text-truncate">Location</th>
              <th class="text-truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logData as $log)
            <tr>
              <td class="text-truncate">
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
              <td class="text-truncate">{{ $general->deviceName($log->client) }}</td>
              <td class="text-truncate">
                 {{-- {{ $general->getIpInfo($log->ip) }} --}}
                 <p>({{$log->ip}}) </p>
              </td>
              <td class="text-truncate">{{ date('Y-m-d h:i A', strtotime($log->created_at)); }}</td>
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
<!-- Edit User Modal -->
<div class="modal fade" id="editUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple modal-edit-user">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Edit User Information</h3>
          <p class="text-muted">Updating user details will receive a privacy audit.</p>
        </div>
        <form id="editUserForm" class="row g-3" onsubmit="return false">
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserFirstName">First Name</label>
            <input type="text" id="modalEditUserFirstName" name="modalEditUserFirstName" class="form-control" placeholder="John" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserLastName">Last Name</label>
            <input type="text" id="modalEditUserLastName" name="modalEditUserLastName" class="form-control" placeholder="Doe" />
          </div>
          <div class="col-12">
            <label class="form-label" for="modalEditUserName">Username</label>
            <input type="text" id="modalEditUserName" name="modalEditUserName" class="form-control" placeholder="john.doe.007" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserEmail">Email</label>
            <input type="text" id="modalEditUserEmail" name="modalEditUserEmail" class="form-control" placeholder="example@domain.com" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserStatus">Status</label>
            <select id="modalEditUserStatus" name="modalEditUserStatus" class="form-select" aria-label="Default select example">
              <option selected>Status</option>
              <option value="1">Active</option>
              <option value="2">Inactive</option>
              <option value="3">Suspended</option>
            </select>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditTaxID">Tax ID</label>
            <input type="text" id="modalEditTaxID" name="modalEditTaxID" class="form-control modal-edit-tax-id" placeholder="123 456 7890" />
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label" for="modalEditUserPhone">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text">US (+1)</span>
              <input type="text" id="modalEditUserPhone" name="modalEditUserPhone" class="form-control phone-number-mask" placeholder="202 555 0111" />
            </div>
          </div>
          <div class="col-12 col-md-6">
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
          <div class="col-12 col-md-6">
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
          <div class="col-12">
            <label class="switch">
              <input type="checkbox" class="switch-input" />
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label">Use as a billing address?</span>
            </label>
          </div>
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Edit User Modal -->

<!-- Enable OTP Modal -->
<div class="modal fade" id="enableOTP" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-simple modal-enable-otp modal-dialog-centered">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Enable One Time Password</h3>
          <p>Verify Your Mobile Number for SMS</p>
        </div>
        <p>Enter your mobile phone number with country code and we will send you a verification code.</p>
        <form id="enableOTPForm" class="row g-3" onsubmit="return false">
          <div class="col-12">
            <label class="form-label" for="modalEnableOTPPhone">Phone Number</label>
            <div class="input-group">
              <span class="input-group-text">US (+1)</span>
              <input type="text" id="modalEnableOTPPhone" name="modalEnableOTPPhone" class="form-control phone-number-otp-mask" placeholder="202 555 0111" />
            </div>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!--/ Enable OTP Modal -->

<!-- Add New Credit Card Modal -->
<div class="modal fade" id="upgradePlanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-simple modal-upgrade-plan">
    <div class="modal-content p-3 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">Upgrade Plan</h3>
          <p>Choose the best plan for user.</p>
        </div>
        <form id="upgradePlanForm" class="row g-3" onsubmit="return false">
          <div class="col-sm-8">
            <label class="form-label" for="choosePlan">Choose Plan</label>
            <select id="choosePlan" name="choosePlan" class="form-select" aria-label="Choose Plan">
              <option selected>Choose Plan</option>
              <option value="standard">Standard - $99/month</option>
              <option value="exclusive">Exclusive - $249/month</option>
              <option value="Enterprise">Enterprise - $499/month</option>
            </select>
          </div>
          <div class="col-sm-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Upgrade</button>
          </div>
        </form>
      </div>
      <hr class="mx-md-n5 mx-n3" />
      <div class="modal-body">
        <p class="mb-0">User current plan is standard plan</p>
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="d-flex justify-content-center me-2">
            <sup class="h6 pricing-currency pt-1 mt-3 mb-0 me-1 text-primary">$</sup>
            <h1 class="display-5 mb-0 text-primary">99</h1>
            <sub class="h5 pricing-duration mt-auto mb-2 text-muted">/month</sub>
          </div>
          <button class="btn btn-label-danger cancel-subscription mt-3">Cancel Subscription</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Add New Credit Card Modal -->

@endsection