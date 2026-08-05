@extends('layouts.main')
@section('title')
Dashboard
@endsection
@section('content')
<h4 class="fw-bold py-3 mb-4">Dashboard</h4>

<div class="row g-6 mb-6">
    <div class="col-sm-6 col-lg-4">
        <div class="card card-border-shadow-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <div class="avatar me-4">
                        <span class="avatar-initial rounded bg-label-primary"><i class="icon-base bx bx-hdd icon-lg"></i></span>
                    </div>
                    <h4 class="mb-0">{{ $general->formatBytes($personal['storage_used']) }}</h4>
                </div>
                <p class="mb-2">My Storage Used</p>
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
                    <h4 class="mb-0">{{ $personal['bucket_count'] }}</h4>
                </div>
                <p class="mb-2">My Buckets</p>
                <a href="{{ route('buckets') }}" class="btn btn-sm btn-outline-primary pjax">Manage Buckets</a>
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
                    <h4 class="mb-0">{{ $personal['object_count'] }}</h4>
                </div>
                <p class="mb-2">My Objects</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <h5 class="card-header">My Recent Uploads</h5>
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
                @foreach ($personal['recent_uploads'] as $object)
                    <tr>
                        <td class="text-truncate" style="max-width: 260px;">{{ $object->object_key }}</td>
                        <td>
                            <a href="{{ route('objects', ['bucket' => $object->bucket->name ?? '']) }}" class="pjax">{{ $object->bucket->name ?? '-' }}</a>
                        </td>
                        <td>{{ $general->formatBytes($object->size) }}</td>
                        <td>{{ $general->dateFormat($object->created_at) }}</td>
                    </tr>
                @endforeach
                @if ($personal['recent_uploads']->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center">No uploads yet. <a href="{{ route('buckets') }}" class="pjax">Create a bucket</a> to get started.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
