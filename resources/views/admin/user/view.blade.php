@extends('admin.layouts.main')
@section('title')
    User View
@endsection
@section('content')
    <style>
        .btn-label-danger {
            display: unset !important;
        }
    </style>
    <div class="breadcrumb-box">
        <h4 class="fw-bold py-3 mb-4">User</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="admin/user" class="pjax">User</a>
                </li>
                <li class="breadcrumb-item active">User View</li>
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
                            <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                src="{{ $general->getFileUrl($model->image, 'profile') }} " height="100" width="100"
                                alt="User avatar" />
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ $model->first_name . ' ' . $model->last_name }}</h4>
                                <span class="badge bg-label-secondary mt-1">Author</span>

                            </div>
                        </div>
                    </div>
                    <small class="card-text text-uppercase text-body-secondary small">Details</small>

                    <div class="info-container">
                        <ul class="list-unstyled my-3 py-1">
                            <li class="d-flex align-items-center mb-4">
                                <i class="icon-base bx bx-user"></i>
                                <span class="fw-medium mx-2">Username:</span>
                                <span>{{ $model->first_name . ' ' . $model->last_name }}</span>
                            </li>
                            <li class="d-flex mb-4">
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
                                @if ($model->status == 0)
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

                        <div class="d-flex justify-content-center mb-3" style="gap: 10px;">
                            <a href="{{ route('admin/user/autologin', ['id' => $model->id]) }}" class="btn btn-primary"
                                style="width: 170px; height: 60px;" title="Login as User">
                                Login as User
                            </a>

                            <a href="{{ route('admin/user/send-tfa-mail', ['id' => $model->id]) }}" class="btn btn-primary"
                                style="width: 170px; height: 60px;" title="Verify Two-Factor Authentication">
                                Re-send Verification Mail
                            </a>
                        </div>
                        <div class="d-flex justify-content-center" style="gap: 10px;">
                            <a href="{{ route('admin/user/update', ['id' => $model->id]) }}" class="btn btn-primary pjax"
                                style="width: 150px; height: 45px;">
                                Edit
                            </a>

                            <button onclick="app.confirmAction(this);"
                                data-action="{{ route('admin/user/delete', ['id' => $model->id]) }}"
                                class="btn btn-label-danger" style="width: 150px; height: 45px;">
                                Delete
                            </button>
                        </div>
                        <div class="mt-3" style="gap: 10px;">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendmail"
                                style="width: 135px; height: 45px;">
                                Send Mail
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /User Card -->
        </div>
        <!--/ User Sidebar -->
        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <!--/ User Pills -->

            <!-- Change Password -->
            <div class="card mb-4">
                <h5 class="card-header">Recent Devices</h5>
                <div class="table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th class="text-truncate">Browser</th>
                                <th class="text-truncate">Device</th>
                                <th class="text-truncate">Location</th>
                                <th class="text-truncate">Recent Activities</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userAuthList as $userAuth)
                                <tr>
                                    <td class="text-truncate">
                                        @if ($userAuth->type == 0)
                                            <strong>Web</strong>
                                        @elseif($userAuth->type == 1)
                                            <strong>Android</strong>
                                        @else
                                            <strong>IOS</strong>
                                        @endif
                                    </td>
                                    <td class="text-truncate">{{ $userAuth->device_uid }}</td>
                                    <td class="text-truncate">{{ $userAuth->ip }}</td>
                                    <td class="text-truncate">{{ $general->dateFormat($userAuth->created_at) }}</td>
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
                <h5 class="card-header">Activity</h5>
                <div class="table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th class="text-truncate">Browser</th>
                                <th class="text-truncate">Device</th>
                                <th class="text-truncate">ip</th>
                                <th class="text-truncate">Recent Activities</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logData as $log)
                                <tr>
                                    <td class="text-truncate">
                                        @if ($log->type == 0)
                                            <strong>Login Fail</strong>
                                        @elseif($log->type == 1)
                                            <strong>Login Success</strong>
                                        @elseif($log->type == 2)
                                            <strong>Login By Remember</strong>
                                        @elseif($log->type == 3)
                                            <strong>Register</strong>
                                        @elseif($log->type == 4)
                                            <strong>Login With Otp</strong>
                                        @elseif($log->type == 5)
                                            <strong>Login With Social Media</strong>
                                        @else
                                            <strong>Register With Social Media</strong>
                                        @endif
                                    </td>
                                    <td class="text-truncate">{{ $log->client }}</td>
                                    <td class="text-truncate">{{ $log->ip }}</td>
                                    <td class="text-truncate">{{ $general->dateFormat($log->created_at) }}</td>
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

    <!-- The Modal -->
    <div class="modal" id="sendmail">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Send Mail</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form method="POST" action="{{ route('admin/user/mail') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $model->user_id }}">
                        <div class="mb-3 mt-3">
                            <label for="to" class="form-label">To :</label>
                            <input type="email" class="form-control" id="to" name="to"
                                value="{{ $model->email }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject :</label>
                            <input type="text" class="form-control" id="subject" placeholder="Enter subject"
                                name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message :</label>
                            <textarea class="form-control" id="message" placeholder="Enter message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Send Mail List -->
    <div class="card mb-4 mt-3">
        <h5 class="card-header">Sent Mails</h5>
        <div class="table-responsive">
            <table class="table border-top">
                <thead>
                    <tr>
                        <th class="text-truncate">ID</th>
                        <th class="text-truncate">To</th>
                        <th class="text-truncate">Subject</th>
                        <th class="text-truncate">Message</th>
                        <th class="text-truncate">Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ContactMessages as $mail)
                        <tr>
                            <td class="text-truncate">{{ $mail->id }}</td>
                            <td class="text-truncate">{{ $mail->to_user }}</td>
                            <td class="text-truncate">{{ $mail->subject }}</td>
                            <td class="text-truncate">{{ Str::limit($mail->message) }}</td>
                            <td class="text-truncate">{{ $general->dateFormat($mail->created_at) }}</td>
                        </tr>
                    @endforeach
                    @if ($ContactMessages->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">No sent emails found.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
