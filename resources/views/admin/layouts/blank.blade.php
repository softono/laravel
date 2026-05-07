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

<html lang="{{ Config::get('app.locale') }}" class="system-style layout-navbar-fixed layout-menu-fixed" dir="ltr"
    data-theme="theme-default" data-theme/assets-path="/tracer/public/theme/assets/"
    data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <base href="{{ URL::to('/') }}/">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title') | {{ config('setting.app_name') }}</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ $general->getFileUrl(config('setting.app_favicon'), 'logo') }}"
        type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="theme/assets/vendor/fonts/iconify-icons.css" />

    <link rel="stylesheet" href="theme/assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="theme/assets/vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="theme/assets/vendor/fonts/flag-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="theme/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="theme/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="theme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="theme/assets/vendor/libs/node-waves/node-waves.css" />
    <link rel="stylesheet" href="theme/assets/vendor/libs/typeahead-js/typeahead.css" />
    <!-- Vendor -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.15.10/sweetalert2.min.css"
        integrity="sha512-Of+yU7HlIFqXQcG8Usdd67ejABz27o7CRB1tJCvzGYhTddCi4TZLVhh9tGaJCwlrBiodWCzAx+igo9oaNbUk5A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="theme/assets/vendor/css/pages/page-auth.css" />

    <!-- Page -->
    <link rel="stylesheet" href="assets/css/common.css" />
    @stack('style')
    <!-- Helpers -->
    <script src="theme/assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="theme/assets/js/config.js"></script>
</head>
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

    window.templateCustomizer.settings.defaultTheme = 'system';
</script>
</head>

<body class="hold-transition login-page">
    <div id="common-loader" style="display:none;">
        <div class="common-loader-conetent">
            <div class="spinner-border spinner-border-lg text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div class="common-loader-backdrop"></div>
    </div>
    <!-- Layout wrapper -->
    <div id="main-container" data-layout="blank">
        <div id="main-content" data-title="@yield('title') | {{ config('app.name') }}">
            @yield('content')
        </div>
    </div>

    <!-- / Layout wrapper -->
    <div id="common-modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content" id="common-modal-content">
            </div>
        </div>
    </div>
    <!-- Toast with Placements -->
    <div id="common-toast"></div>
    <!-- Toast with Placements -->
    <!-- Core JS -->
    <!-- build:js theme/assets/vendor/js/core.js -->
    <script src="theme/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="theme/assets/vendor/libs/popper/popper.js"></script>
    <script src="theme/assets/vendor/js/bootstrap.js"></script>
    <script src="theme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="theme/assets/vendor/libs/node-waves/node-waves.js"></script>
    <script src="theme/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="theme/assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <!-- endbuild -->
    <!-- Vendors JS -->
    <!-- Main JS -->
    <script src="theme/assets/js/main.js"></script>

    <!-- Page JS -->
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
