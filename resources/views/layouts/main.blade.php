<?php
if (isset($_GET['partial']) && $_GET['partial']) {
    if (isset($_GET['layout']) && $_GET['layout'] == 'main') {
        $metaData = $general->getMetaData();
?>
        <div id="main-content" data-title="@php if($metaData['title']){echo $metaData['title'];}else{ @endphp @yield('title') | {{config('setting.app_name')}}@php } @endphp">
            {{ view('common/message_alert') }}
            @stack('styles')
            @yield('content')
            @stack('scripts')
        </div>
    <?php
    } else {
        echo 'reload';
    }
} else {
    $metaData = $general->getMetaData();
    $sessionUser = false;
    if (!auth()->guest()) {
        $sessionUser = auth()->user();
    }
    ?>
    <!DOCTYPE html>
    <html lang="{{ Config::get('app.locale') }}" class="layout-navbar-fixed layout-wide" data-assets-path="theme/assets/" data-template="front-pages">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
        <base href="{{ URL::to('/') }}/">
        <meta http-equiv="Content-Language" content="{{ Config::get('app.locale') }}">
        @if($metaData['title'])
        <title>{{$metaData['title']}}</title>
        <meta name="keywords" content="{{$metaData['keyword']}}">
        <meta name="description" content="{{$metaData['description']}}">
        @else
        <title>@yield('title') | {{config('setting.app_name')}}</title>
        @endif
        <link rel="shortcut icon"  href="{{ $general->getFileUrl(config('setting.app_favicon'),'logo') }}" type="image/x-icon">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        
        <!-- Icons -->
        <link rel="stylesheet" href="theme/assets/vendor/fonts/iconify-icons.css" />
        <link rel="stylesheet" href="theme/assets/vendor/libs/pickr/pickr-themes.css">
        <link rel="stylesheet" href="theme/assets/vendor/css/core.css" class="template-customizer-core-css  " />
        <link rel="stylesheet" href="theme/assets/css/demo.css" />
        <!-- Core CSS -->
        <!-- Vendors CSS -->
        <link rel="stylesheet" href="theme/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

        <!-- Page CSS -->
        @vite(['resources/css/app.css'])
        <!-- Helpers -->
        <script src="theme/assets/vendor/js/helpers.js"></script>
        <script src="theme/assets/vendor/js/template-customizer.js"></script>
        <script src="theme/assets/js/front-config.js"></script>
        @stack('styles')
        <script>
            /*Global variables*/
            var APP_UID = '{{config("setting.app_uid")}}';
            var APP_URL = "{{ URL::to('/') }}/";
            var CSRF_NAME = '_token';
            var CSRF_TOKEN = "{{ Session::token() }}";
            var dataTableObj = false;
            var documentReadyFunctions = [];

            function documentReady(fn) {
                documentReadyFunctions.push(fn);
            }
        </script>
        {!! config('setting.header_content') !!}
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
        <div class="layout-wrapper layout-content-navbar layout-without-menu">
            <!-- Layout container -->
            <div class="layout-container">
                <!-- Layout page -->
                <div class="layout-page">
                    <!-- Navbar -->
                    {{ view('layouts/component/main_navbar',compact('sessionUser')) }}
                    <!-- / Navbar -->

                    <!-- Content wrapper -->
                    <div class="content-wrapper">
                        <!-- Content -->
                        <div class="container-xxl flex-grow-1 container-p-y">
                            <div id="main-container" data-layout="main">
                                <div id="main-content" data-title="@php if($metaData['title']){echo $metaData['title'];}else{ @endphp@yield('title') | {{config('setting.app_name')}}@php }@endphp">
                                    {{ view('common/message_alert') }}
                                    @yield('content')
                                </div>
                            </div>
                        </div>
                        <!--/ Content -->
                        <!-- Footer -->
                        <footer class="content-footer footer bg-footer-theme">
                            <div class="container-xxl">
                                <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
                                    <div class="mb-2 mb-md-0">
                                        ©{{date('Y')}} , made by <a href="{{route('home')}}" target="_blank" class="fw-semibold footer-link">{{ config('setting.app_name') }}</a>
                                    </div>
                                    <div class="d-none d-lg-inline-block">
                                        <a target="_blank" href="page/terms" class="footer-link me-4 pjax">Terms & Condition</a>
                                        <a target="_blank" href="page/privacy-policy" class="footer-link pjax">Privacy Policy</a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                        <!-- / Footer -->
                        <div class="content-backdrop fade"></div>
                    </div>
                    <!--/ Content wrapper -->
                </div>
                <!--/ Layout page -->
            </div>
            <!--/ Layout container -->
            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
            <!-- Drag Target Area To SlideIn Menu On Small Screens -->
            <div class="drag-target"></div>
        </div>
        <!--/ Layout wrapper -->
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
        {{ view('common/cookie_consent') }}
        <!-- build:js assets/vendor/js/core.js -->
        <script src="theme/assets/vendor/libs/jquery/jquery.js"></script>
        <script src="theme/assets/vendor/libs/popper/popper.js"></script>
        <script src="theme/assets/vendor/js/bootstrap.js"></script>
        <script src="theme/assets/js/front-main.js"></script>
        <!-- Main JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="assets/js/common.js"></script>
        <script src="assets/js/app.js"></script>
        @stack('scripts')
        {!! config('setting.footer_content') !!}
        
        <script src="assets/js/pjax.js"></script>
    </body>

    </html>
<?php } ?>