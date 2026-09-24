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
<html lang="en">

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

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

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
    </script>
</head>

<body class="bg-slate-50" x-data="{ sidebarOpen: false }">
    <div id="common-loader" class="fixed inset-0 z-[9999] hidden items-center justify-center">
        <div class="common-loader-backdrop"></div>
        <div class="common-loader-conetent">
            <i class="bx bx-loader-alt animate-spin text-4xl text-primary-600"></i>
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <!-- Layout wrapper -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        {{ view('admin/layouts/component/main_sidebar', compact('sessionUser')) }}
        <!-- / Sidebar -->

        <div class="flex min-h-screen w-full flex-1 flex-col lg:ps-64">
            <!-- Navbar -->
            {{ view('admin/layouts/component/main_navbar', compact('sessionUser')) }}
            <!-- / Navbar -->

            <div class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div id="main-container" data-layout="main">
                    <div id="main-content" data-title="@yield('title') | {{ config('setting.app_name') }}">
                        {{ view('common/message_alert') }}
                        @yield('content')
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="border-t border-slate-200 bg-white">
                <div class="px-4 py-4 text-sm text-slate-500 sm:px-6 lg:px-8">
                    ©{{ date('Y') }}, made by
                    <a href="admin/dashboard" target="_self" class="pjax font-medium text-slate-700 hover:text-primary-600">{{ config('setting.app_name') }}</a>
                </div>
            </footer>
            <!-- / Footer -->
        </div>
    </div>

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
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js"></script>

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
