@extends('layouts.main')
@section('title')
Bucket Statistics
@endsection
@section('content')
<h4 class="fw-bold py-3 mb-4">{{ $bucket->name }}</h4>

<div class="card mb-4">
    <div class="card-body">
        <ul class="list-unstyled my-3 py-1">
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
        <a href="{{ route('objects', ['bucket' => $bucket->name]) }}" class="btn btn-primary pjax">Browse Objects</a>
        <a href="{{ route('buckets/update', ['id' => $bucket->id]) }}" class="btn btn-outline-secondary pjax">Edit</a>
        <a href="{{ route('buckets') }}" class="btn btn-outline-secondary pjax">Back</a>
    </div>
</div>
@endsection
