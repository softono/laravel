@extends('admin.layouts.main')
@section('title')
    Admin View
@endsection
@section('content')
    <style>
        .btn-label-danger {
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
                            <img class="img-fluid rounded mb-3 pt-1 mt-4"
                                src="{{ $general->getFileUrl($model->image, 'profile') }}" height="100" width="100"
                                alt="User avatar" />
                            <div class="user-info text-center">
                                <h4 class="mb-2">{{ $model->first_name . ' ' . $model->last_name }}</h4>
                                @if ($model->isSuperAdmin())
                                    <span class="badge bg-label-primary mt-1">Super Admin</span>
                                @else
                                    <span class="badge bg-label-success mt-1">Admin</span>
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
                                <span>{{ $model->first_name . ' ' . $model->last_name }}</span>
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
                                @if ($model->isActive())
                                    <span class="badge rounded-pill bg-label-success">Active</span>
                                @else
                                    <span class="badge rounded-pill bg-label-danger">Inactive</span>
                                @endif
                            </li>
                            <li class="d-flex align-items-center mb-4">
                                <i class="icon-base bx bx-time"></i>
                                <span class="fw-medium mx-2">Created at:</span>
                                <span>{{ $general->dateFormat($model->created_at) }}</span>
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
                            <a href="{{ route('admin/admin/update', ['id' => $model->id]) }}"
                                class="btn btn-primary me-3 pjax">Edit</a>
                            <button onclick="app.confirmAction(this);" data-id="{{ $model->id }}"
                                data-action="{{ route('admin/admin/delete') }}" class="btn btn-label-danger">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /User Card -->
        </div>
        <!--/ User Sidebar -->

        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <!-- Recent Devices -->
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-header mb-0">Trusted Devices</h5>
                    <a href="admin/device" class="btn btn-sm btn-primary me-3 pjax">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th class="text-truncate">User Agent</th>
                                <th class="text-truncate">IP</th>
                                <th class="text-truncate">Trusted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($devices as $device)
                                <tr>
                                    <td class="text-truncate">{{ $device->user_agent }}</td>
                                    <td class="text-truncate">{{ $device->ip_address }}</td>
                                    <td class="text-truncate">{{ $general->dateFormat($device->created_at) }}</td>
                                </tr>
                            @endforeach
                            @if ($devices->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center">No devices found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity -->
            <div class="card mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-header mb-0">Activity</h5>
                    <a href="admin/user-activity" class="btn btn-sm btn-primary me-3 pjax">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table border-top">
                        <thead>
                            <tr>
                                <th class="text-truncate">Activity</th>
                                <th class="text-truncate">Client</th>
                                <th class="text-truncate">IP</th>
                                <th class="text-truncate">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activities as $log)
                                <tr>
                                    <td class="text-truncate">{{ \App\Constants\UserActivity::label($log->type) }}</td>
                                    <td class="text-truncate">{{ $log->client }}</td>
                                    <td class="text-truncate">{{ $log->ip }}</td>
                                    <td class="text-truncate">{{ $general->dateFormat($log->created_at) }}</td>
                                </tr>
                            @endforeach
                            @if ($activities->isEmpty())
                                <tr>
                                    <td colspan="4" class="text-center">No activity found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!--/ Recent Devices -->
        </div>
        <!--/ User Content -->
    </div>
@endsection
