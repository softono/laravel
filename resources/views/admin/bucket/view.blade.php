@extends('admin.layouts.main')
@section('title')
Bucket View
@endsection
@section('content')
<div class="breadcrumb-box">
  <h4 class="fw-bold py-3 mb-4">Bucket</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax">Dashboard</a>
      </li>
      <li class="breadcrumb-item">
        <a href="admin/bucket" class="pjax">Buckets</a>
      </li>
      <li class="breadcrumb-item active">Bucket View</li>
    </ol>
  </nav>
</div>

<div class="card mb-4">
  <div class="card-body">
    <ul class="list-unstyled my-3 py-1">
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Name:</span>
        <span>{{ $bucket->name }}</span>
      </li>
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Owner:</span>
        <span>{{ $bucket->user->first_name }} {{ $bucket->user->last_name }} ({{ $bucket->user->email }})</span>
      </li>
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Visibility:</span>
        @if ($bucket->isPublic())
          <span class="badge rounded-pill bg-label-success">Public</span>
        @else
          <span class="badge rounded-pill bg-label-secondary">Private</span>
        @endif
      </li>
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Objects:</span>
        <span>{{ $stats['object_count'] }}</span>
      </li>
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Storage Used:</span>
        <span>{{ $general->formatBytes($stats['storage_used']) }}</span>
      </li>
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Quota:</span>
        <span>{{ $bucket->storage_quota ? $general->formatBytes($bucket->storage_quota) : 'Unlimited (not enforced)' }}</span>
      </li>
      <li class="d-flex align-items-center mb-4">
        <span class="fw-medium me-2">Created:</span>
        <span>{{ $general->dateFormat($bucket->created_at) }}</span>
      </li>
    </ul>
  </div>
</div>
@endsection
