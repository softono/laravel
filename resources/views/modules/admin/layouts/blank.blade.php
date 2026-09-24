<?php
if (isset($_GET['partial']) && $_GET['partial']) {
  if (isset($_GET['layout']) && $_GET['layout'] == 'blank') {
?>
<div id="main-content" data-title="@yield('title') | {{ Config::get('setting.app_name') }}">
    {{ view('common/message_alert') }}
    @stack('styles')
    @yield('content')
    @stack('scripts')
</div>
<?php } else {
    echo 'reload';
  }
} else {
  ?>
<!DOCTYPE html>

<html lang="{{ Config::get('app.locale') }}" dir="ltr">

<head>
    <meta charset="utf-8" />
    <base href="{{ URL::to('/') }}/">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('setting.app_name') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ $general->getFileUrl(config('setting.app_favicon'), 'logo') }}"
        type="image/x-icon">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.15.10/sweetalert2.min.css"
        integrity="sha512-Of+yU7HlIFqXQcG8Usdd67ejABz27o7CRB1tJCvzGYhTddCi4TZLVhh9tGaJCwlrBiodWCzAx+igo9oaNbUk5A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('style')

    <script>
        /*Global variables*/
        var APP_UID = '{{ config('setting.app_uid') }}';
        var CSRF_NAME = '_token';
        var CSRF_TOKEN = "{{ Session::token() }}";
        var APP_URL = "{{ URL::to('/') }}/";
        var dataTableObj = false;
        var documentReadyFunctions = [];

        function documentReady(fn) {
            documentReadyFunctions.push(fn);
        }
    </script>
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-100 px-4 py-10">
    <div id="common-loader" class="fixed inset-0 z-[9999] hidden items-center justify-center">
        <div class="common-loader-backdrop"></div>
        <div class="common-loader-conetent">
            <i class="bx bx-loader-alt animate-spin text-4xl text-primary-600"></i>
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Layout wrapper -->
    <div id="main-container" data-layout="blank" class="w-full">
        <div id="main-content" data-title="@yield('title') | {{ config('app.name') }}">
            @yield('content')
        </div>
    </div>

    <!-- / Layout wrapper -->
    <div id="common-modal" class="modal fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="modal-backdrop" data-modal-dismiss></div>
        <div class="modal-dialog relative z-10 w-full max-w-lg">
            <div class="modal-content" id="common-modal-content"></div>
        </div>
    </div>
    <!-- Toast with Placements -->
    <div id="common-toast" class="fixed top-4 end-4 z-[9999] flex flex-col items-end gap-2"></div>
    <!-- Toast with Placements -->
    <!-- Core JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"
        integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="assets/js/common.js"></script>
    <script src="assets/js/app.js"></script>
    @stack('scripts')
    <script src="assets/js/pjax.js"></script>
</body>

</html>
<?php } ?>
