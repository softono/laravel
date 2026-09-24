@extends('admin.layouts.main')
@section('title')
Seo meta
@endsection
@section('content')
<?php $sessionUser = auth()->user(); ?>
<!-- Content -->
<div class="breadcrumb-box">
  <h4 class="text-xl font-bold text-slate-800">Seo meta</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
      </li>
      <li class="breadcrumb-item active">Seo meta</li>
    </ol>
  </nav>
</div>

<!-- Seo Meta List Table -->
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Seo Meta</h5>
    <div class="flex items-center gap-2">
      @if ($sessionUser->hasPermission('admin/seo/sitemap-generate'))
        <button type="button" class="btn-label-primary" aria-label="Generate Sitemap" data-modal-open="#sitemapmodel">
          <span>Sitemap</span>
        </button>
      @endif
      @if ($sessionUser->hasPermission('admin/seo/create'))
        <a href="admin/seo/create" class="btn-primary pjax" aria-label="Create SEO Meta">
          <i class="bx bx-plus"></i>
          <span>Create</span>
        </a>
      @endif
    </div>
  </div>

  <div class="card-body overflow-x-auto">
    <table class="w-full text-sm" id="seo-data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Url</th>
          <th>Title</th>
          <th>Keyword</th>
          <th>Sitemap</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- / Content -->

<!-- Sitemap Modal -->
<div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="sitemapmodel">
  <div class="modal-backdrop" data-modal-dismiss></div>
  <div class="modal-dialog relative z-10 w-full max-w-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">SiteMap</h5>
        <button type="button" class="btn-close" data-modal-dismiss aria-label="Close">
          <i class="bx bx-x"></i>
        </button>
      </div>
      <div class="modal-body">
        SiteMap URL:
        <a href="{{ url('sitemap.xml') }}" class="noroute pjax text-primary-600 hover:underline" target="_blank">{{ url('sitemap.xml') }}</a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-primary" data-modal-dismiss
          onclick="app.ajaxGet('admin/seo/sitemap-update');">Update SiteMap</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<!-- DataTable Init -->
<script>
  documentReady(function () {
    datatableObj = $('#seo-data-table').DataTable({
      ajax: dataTableAjax({
        url: '{{ route("admin/seo/list") }}',
        method: 'post'
      }),
      columns: [
        { data: "id", responsivePriority: 6 },
        { data: "url", responsivePriority: 6 },
        { data: "title", responsivePriority: 6 },
        { data: "keyword", responsivePriority: 4 },
        { data: "sitemap_enable", responsivePriority: 4 },
        { data: "action", sortable: false, responsivePriority: 2 }
      ],
      responsive: true,
      serverSide: true,
      order: [[0, "desc"]]
    });
  });
</script>
@endpush
