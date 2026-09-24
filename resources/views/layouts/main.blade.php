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
    <html lang="{{ Config::get('app.locale') }}">

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
        <link rel="shortcut icon" href="{{ $general->getFileUrl(config('setting.app_favicon'),'logo') }}" type="image/x-icon">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        @include('common.datatables-css')
        @include('common.theme-init')
        @vite('resources/css/app.css')
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

    <body class="flex min-h-screen flex-col bg-background">
        <div id="common-loader" class="fixed inset-0 z-[9999] hidden items-center justify-center" role="status">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative text-center">
        <i class="bx bx-loader-alt animate-spin text-4xl text-white"></i>
        <span class="sr-only">Loading...</span>
    </div>
</div>

        <!-- Navbar -->
        {{ view('layouts/component/main_navbar', compact('sessionUser')) }}
        <!-- / Navbar -->

        <!-- Content -->
        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div id="main-container" data-layout="main">
                <div id="main-content" data-title="@php if($metaData['title']){echo $metaData['title'];}else{ @endphp@yield('title') | {{config('setting.app_name')}}@php }@endphp">
                    {{ view('common/message_alert') }}
                    @yield('content')
                </div>
            </div>
        </main>
        <!--/ Content -->

        <!-- Footer -->
        <footer class="border-t border-border bg-card">
            <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-2 px-4 py-4 text-sm text-muted-foreground sm:flex-row sm:px-6 lg:px-8">
                <div>
                    ©{{date('Y')}}, made by <a href="{{route('home')}}" target="_blank" class="font-semibold text-foreground hover:underline">{{ config('setting.app_name') }}</a>
                </div>
                <div class="hidden gap-4 sm:flex">
                    <a target="_blank" href="page/terms" class="pjax hover:text-foreground">Terms &amp; Condition</a>
                    <a target="_blank" href="page/privacy-policy" class="pjax hover:text-foreground">Privacy Policy</a>
                </div>
            </div>
        </footer>
        <!-- / Footer -->

        <x-ui.modal id="common-modal" content-id="common-modal-content"></x-ui.modal>
        <!-- Toast placement -->
        <div id="common-toast" class="fixed top-4 right-4 z-[9999] flex flex-col items-end gap-2"></div>
        <!-- Core JS -->
        {{ view('common/cookie_consent') }}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        @include('common.datatables-js')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="assets/js/app.js"></script>
        @stack('scripts')
        {!! config('setting.footer_content') !!}

        <script src="assets/js/pjax.js"></script>
    </body>

    </html>
<?php } ?>
