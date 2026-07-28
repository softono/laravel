<?php
if (isset($_GET['partial']) && $_GET['partial']) {
    if (isset($_GET['layout']) && $_GET['layout'] == 'main') {
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
    $sessionUser = false;
    if (!auth()->guest()) {
        $sessionUser = auth()->user();
    }
    ?>

<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="theme/assets/"
    data-template="vertical-menu-template">

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
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="theme/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="theme/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="theme/assets/css/demo.css" />

    <!-- pro feature css -->
    <link rel="stylesheet" href="theme/assets/vendor/libs/pickr/pickr-themes.css">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="theme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="theme/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet" href="theme/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />
    <link rel="stylesheet" href="theme/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css" />
    <link rel="stylesheet" href="theme/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css" />
    <link rel="stylesheet" href="assets/css/common.css" />

    @stack('style')

    <script>
        /*Global variables*/
        var APP_UID = '{{ config('setting.app_uid') }}';
        var APP_URL = "{{ URL::to('/') }}/";
        var CSRF_NAME = '_token';
        var CSRF_TOKEN = "{{ Session::token() }}";
        var dataTableObj = false;
        var documentReadyFunctions = [];

        function documentReady(fn) {
            documentReadyFunctions.push(fn);
        }
        window.templateCustomizer = false;
    </script>

    <!-- Helpers -->
    <script src="theme/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!-- pro feature js -->
    <script src="theme/assets/vendor/js/template-customizer.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="theme/assets/js/config.js"></script>

</head>

<body>
    <div id="common-loader" style="display:none;">
        <div class="common-loader-conetent">
            <div class="spinner-border spinner-border-lg text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div class="common-loader-backdrop"></div>
    </div>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            {{ view('admin/layouts/component/main_sidebar', compact('sessionUser')) }}
            <!-- / Menu -->
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                {{ view('admin/layouts/component/main_navbar', compact('sessionUser')) }}
                <!-- / Navbar -->
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div id="main-container" data-layout="main">
                            <div id="main-content" data-title="@yield('title') | {{ config('setting.app_name') }}">
                                {{ view('common/message_alert') }}
                                @yield('content')
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                <div class="mb-2 mb-md-0">
                                    ©{{ date('Y') }}, made by <a href="admin/dashboard" target="_self"
                                        class="footer-link pjax">{{ config('setting.app_name') }}</a>
                                </div>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>
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
    <script src="theme/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="theme/assets/vendor/libs/popper/popper.js"></script>
    <script src="theme/assets/vendor/js/bootstrap.js"></script>
    <script src="theme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="theme/assets/vendor/js/menu.js"></script>

    <script src="theme/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="theme/assets/vendor/libs/pickr/pickr.js"></script>
    <script src="theme/assets/vendor/libs/moment/moment.js"></script> -->
    <script src="theme/assets/vendor/libs/flatpickr/flatpickr.js"></script>

    <script src="theme/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="theme/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="theme/assets/vendor/libs/typeahead-js/typeahead.js"></script>

    <script src="theme/assets/js/main.js"></script>

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
