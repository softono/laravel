@extends('admin.layouts.main')
@section('title')
Dashboard
@endsection
@section('content')
<div class="breadcrumb-box">
  <h4 class="fw-bold py-3 mb-4">Dashboard</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax">Dashboard</a>
      </li>
      <li class="breadcrumb-item active">Dashboard</li>
    </ol>
  </nav>
</div>

<div class="row g-6 mb-6">
  <div class="col-sm-6 col-lg-4">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-hdd icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{ $general->formatBytes($systemWide['total_storage_used']) }}</h4>
        </div>
        <p class="mb-2">Total Storage Used</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="card card-border-shadow-success h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-success"><i class="icon-base bx bx-archive icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{ $systemWide['total_buckets'] }}</h4>
        </div>
        <p class="mb-2">Total Buckets</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-info"><i class="icon-base bx bx-file icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{ $systemWide['total_objects'] }}</h4>
        </div>
        <p class="mb-2">Total Objects</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-warning"><i class="icon-base bx bx-user icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{ $systemWide['total_users'] }}</h4>
        </div>
        <p class="mb-2">Total Bucket Admins</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-danger"><i class="icon-base bx bx-key icon-lg"></i></span>
          </div>
          <h4 class="mb-0">{{ $systemWide['total_api_keys'] }}</h4>
        </div>
        <p class="mb-2">Total API Keys</p>
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <div class="col-lg-6">
    <div class="card h-100">
      <h5 class="card-header">Per-User Storage Breakdown</h5>
      <div class="table-responsive">
        <table class="table border-top">
          <thead>
            <tr>
              <th>User</th>
              <th>Buckets</th>
              <th>Objects</th>
              <th>Storage Used</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($perUser as $row)
              <tr>
                <td>{{ $row->first_name }} {{ $row->last_name }}<br><small class="text-muted">{{ $row->email }}</small></td>
                <td>{{ $row->bucket_count }}</td>
                <td>{{ $row->object_count }}</td>
                <td>{{ $general->formatBytes($row->storage_used) }}</td>
              </tr>
            @endforeach
            @if ($perUser->isEmpty())
              <tr>
                <td colspan="4" class="text-center">No Bucket Admin users yet.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <h5 class="card-header">Recent Uploads</h5>
      <div class="table-responsive">
        <table class="table border-top">
          <thead>
            <tr>
              <th>Object Key</th>
              <th>Bucket</th>
              <th>Size</th>
              <th>Uploaded</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($systemWide['recent_uploads'] as $object)
              <tr>
                <td class="text-truncate" style="max-width: 220px;">{{ $object->object_key }}</td>
                <td>{{ $object->bucket->name ?? '-' }}</td>
                <td>{{ $general->formatBytes($object->size) }}</td>
                <td>{{ $general->dateFormat($object->created_at) }}</td>
              </tr>
            @endforeach
            @if ($systemWide['recent_uploads']->isEmpty())
              <tr>
                <td colspan="4" class="text-center">No uploads yet.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
