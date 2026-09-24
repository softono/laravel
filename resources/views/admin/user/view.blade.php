@extends('modules.admin.layouts.main')
@section('title')
    User View
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">User</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="admin/user" class="pjax hover:text-primary-600">User</a>
                </li>
                <li class="breadcrumb-item active">User View</li>
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
                        <img class="rounded mb-3 pt-1 mt-4"
                            src="{{ $general->getFileUrl($model->image, 'profile') }} " height="100" width="100"
                            alt="User avatar" />
                        <div class="text-center">
                            <h4 class="mb-2 text-lg font-semibold text-slate-800">{{ $model->first_name . ' ' . $model->last_name }}</h4>
                            <span class="badge-soft-secondary mt-1">Author</span>
                        </div>
                    </div>
                    <small class="block text-xs uppercase text-slate-500 mt-3">Details</small>

                    <div class="mt-3">
                        <ul class="list-none my-3 py-1 space-y-4">
                            <li class="flex items-center">
                                <i class="bx bx-user"></i>
                                <span class="font-medium mx-2">Username:</span>
                                <span>{{ $model->first_name . ' ' . $model->last_name }}</span>
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
                                @if ($model->status == 0)
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

                        <div class="flex justify-center gap-2.5 mb-3">
                            <a href="{{ route('admin/user/autologin', ['id' => $model->id]) }}" class="btn-primary"
                                style="width: 170px; height: 60px;" title="Login as User">
                                Login as User
                            </a>

                            <a href="{{ route('admin/user/send-tfa-mail', ['id' => $model->id]) }}" class="btn-primary"
                                style="width: 170px; height: 60px;" title="Verify Two-Factor Authentication">
                                Re-send Verification Mail
                            </a>
                        </div>
                        <div class="flex justify-center gap-2.5">
                            <a href="{{ route('admin/user/update', ['id' => $model->id]) }}" class="btn-primary pjax"
                                style="width: 150px; height: 45px;">
                                Edit
                            </a>

                            <button onclick="app.confirmAction(this);"
                                data-action="{{ route('admin/user/delete', ['id' => $model->id]) }}"
                                class="btn-label-danger" style="width: 150px; height: 45px;">
                                Delete
                            </button>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn-primary" data-modal-open="#sendmail"
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
        <div class="w-full lg:w-[calc(58.3333%-1rem)]">
            <!--/ User Pills -->

            <!-- Change Password -->
            <div class="card mb-4">
                <h5 class="card-header card-title">Recent Devices</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-t border-slate-200">
                        <thead>
                            <tr>
                                <th class="truncate">Browser</th>
                                <th class="truncate">Device</th>
                                <th class="truncate">Location</th>
                                <th class="truncate">Recent Activities</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userAuthList as $userAuth)
                                <tr>
                                    <td class="truncate">
                                        @if ($userAuth->type == 0)
                                            <strong>Web</strong>
                                        @elseif($userAuth->type == 1)
                                            <strong>Android</strong>
                                        @else
                                            <strong>IOS</strong>
                                        @endif
                                    </td>
                                    <td class="truncate">{{ $userAuth->device_uid }}</td>
                                    <td class="truncate">{{ $userAuth->ip }}</td>
                                    <td class="truncate">{{ $general->dateFormat($userAuth->created_at) }}</td>
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
                <h5 class="card-header card-title">Activity</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-t border-slate-200">
                        <thead>
                            <tr>
                                <th class="truncate">Browser</th>
                                <th class="truncate">Device</th>
                                <th class="truncate">ip</th>
                                <th class="truncate">Recent Activities</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logData as $log)
                                <tr>
                                    <td class="truncate">
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
                                    <td class="truncate">{{ $log->client }}</td>
                                    <td class="truncate">{{ $log->ip }}</td>
                                    <td class="truncate">{{ $general->dateFormat($log->created_at) }}</td>
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
    <div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="sendmail">
        <div class="modal-backdrop" data-modal-dismiss></div>
        <div class="modal-dialog relative z-10 w-full max-w-2xl">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Send Mail</h4>
                    <button type="button" class="btn-close" data-modal-dismiss aria-label="Close">
                        <i class="bx bx-x text-xl"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <form method="POST" action="{{ route('admin/user/mail') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $model->user_id }}">
                        <div class="mb-3">
                            <label for="to" class="form-label">To :</label>
                            <input type="email" class="form-input" id="to" name="to"
                                value="{{ $model->email }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject :</label>
                            <input type="text" class="form-input" id="subject" placeholder="Enter subject"
                                name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message :</label>
                            <textarea class="form-input" id="message" placeholder="Enter message" name="message" rows="5" required></textarea>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-primary">Submit</button>
                            <button type="button" class="btn-danger" data-modal-dismiss>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Send Mail List -->
    <div class="card mb-4 mt-3">
        <h5 class="card-header card-title">Sent Mails</h5>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-t border-slate-200">
                <thead>
                    <tr>
                        <th class="truncate">ID</th>
                        <th class="truncate">To</th>
                        <th class="truncate">Subject</th>
                        <th class="truncate">Message</th>
                        <th class="truncate">Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ContactMessages as $mail)
                        <tr>
                            <td class="truncate">{{ $mail->id }}</td>
                            <td class="truncate">{{ $mail->to_user }}</td>
                            <td class="truncate">{{ $mail->subject }}</td>
                            <td class="truncate">{{ Str::limit($mail->message) }}</td>
                            <td class="truncate">{{ $general->dateFormat($mail->created_at) }}</td>
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
