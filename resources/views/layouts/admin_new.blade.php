@php use Carbon\Carbon; @endphp
    <!DOCTYPE html>

<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{asset('')}}"
    data-template="vertical-menu-template">
<head>
    <meta charset="utf-8"/>
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

    <title>@yield('title', config('app.name')) - {{ config('app.name') }}</title>

    <meta name="description" content=""/>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset('favicon.ico')}}"/>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
        rel="stylesheet"/>

    <!-- Icons -->
    <link rel="stylesheet" href="{{asset('main/vendor/fonts/remixicons/remixicon.min.css')}}"/>

    <!-- Menu waves for no-customizer fix -->
    {{--    <link rel="stylesheet" href="{{asset('main/vendor/libs/node-waves/node-waves.css')}}"/>--}}

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{asset('main/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}"/>
    <link rel="stylesheet" href="{{asset('main/vendor/libs/sweetalert2/sweetalert2.css')}}"/>
    <link rel="stylesheet" href="{{asset('main/vendor/libs/spinkit/spinkit.css')}}"/>

    <!-- Page CSS -->
    @yield('style')

    <!-- Core CSS -->
{{--    <link rel="stylesheet" href="{{asset('css/demo.css')}}"/>--}}
    <link rel="stylesheet" href="{{asset('main/vendor/css/status.min.css')}}"/>

    <link rel="stylesheet" href="{{asset('main/vendor/css/core.min.css')}}" class="template-customizer-core-css"/>
    <link rel="stylesheet" href="{{asset('main/vendor/css/theme-default.css')}}" class="template-customizer-theme-css"/>

    <style>
        .form-fieldset {
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--bs-body-bg);
            border: var(--bs-border-width) var(--bs-border-style) var(--bs-border-color);
            border-radius: 0.625rem;
            /*box-shadow: 0 .25rem .875rem 0 rgba(16, 17, 33, .26);*/
        }

        .modal-blur {
            -webkit-backdrop-filter: blur(4px);
            backdrop-filter: blur(4px);
        }

        .transparent-swal2 .swal2-popup {
            background-color: transparent !important; /* Make dialog background transparent */
            box-shadow: none !important; /* Remove box-shadow */
        }

        .swal2-container.transparent-swal2 {
            background-color: rgba(48, 51, 78, .3) !important;
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }

        .table_dt_no {
            width: 25px !important;
            max-width: 25px !important;
            min-width: 25px !important;
        }
    </style>
    <!-- Helpers -->
    <script src="{{asset('main/vendor/js/helpers.js')}}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{asset('main/vendor/js/template-customizer.js')}}"></script>
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{asset('js/config.js')}}"></script>

</head>

<body>
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->
        @include('layouts.adminMenu_new')

        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->

            <nav
                class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                        <i class="ri-menu-fill ri-22px"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <div class="navbar-nav align-items-center d-xs-none">
                        <span class="d-none d-md-inline-block">
                            <span>{{config('app.name')}} - </span>
                            <span>{{ Carbon::now()->locale('id_ID')->translatedFormat('l, d F Y') }} - </span>
                            <span id="clock"></span>
                        </span>
                    </div>

                    <ul class="navbar-nav flex-row align-items-center ms-auto">

                        <!-- Style Switcher -->
                        <li class="nav-item dropdown-style-switcher dropdown me-1 me-xl-0">
                            <a class="nav-link btn btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow"
                               href="javascript:void(0);" data-bs-toggle="dropdown">
                                <i class='ri-22px'></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                                        <span class="align-middle"><i class='ri-sun-line ri-22px me-3'></i>Terang</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                                        <span class="align-middle"><i
                                                class="ri-moon-clear-line ri-22px me-3"></i>Gelap</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                                        <span class="align-middle"><i
                                                class="ri-computer-line ri-22px me-3"></i>Sistem</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- / Style Switcher-->

                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                               data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    <img src="{{asset('logo.png')}}" alt class="rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar avatar-online">
                                                    <img src="{{asset('logo.png')}}" alt
                                                         class="rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-medium d-block small">{{config('app.name')}}</span>
                                                <small class="text-muted"> {{Auth::user()->name??'admin'}}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
{{--                                <li>--}}
{{--                                    <a class="dropdown-item" href="#">--}}
{{--                                        <i class="ri-user-3-line ri-22px me-3"></i><span--}}
{{--                                            class="align-middle">Profil</span>--}}
{{--                                    </a>--}}
{{--                                </li>--}}
                                <li>
                                    <div class="d-grid px-4 pt-2 pb-1">
                                        <a class="btn btn-sm btn-danger d-flex" href="{{route('logout')}}">
                                            <small class="align-middle">Logout</small>
                                            <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </li>                        <!--/ User -->
                    </ul>
                </div>
            </nav>

            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="flex-grow-1 container-p-y container-fluid">
                    @yield('content')
                </div>
                <!-- / Content -->

                <!-- Footer -->
                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div
                            class="footer-container d-flex align-items-center justify-content-between py-3 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                ©
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                , made with <span class="text-danger"><i class="tf-icons ri ri-heart-3-fill"></i></span>
                                by
                                <a href="{{route('admin.index')}}" target="_blank"
                                   class="footer-link fw-medium">PT. Inovasi Cipta Teknologi</a>
                            </div>
                            <div class="d-none d-lg-inline-block">
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- / Footer -->

                <button type="button" href="#" id="backToTopBtn" class="btn btn-lg px-3 back-to-top " role="button"
                        aria-label="Ke Atas" title="Ke Atas">
                    <i class="ri ri-2x ri-arrow-up-line"></i>
                </button>

                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
</div>
<!-- / Layout wrapper -->

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{asset('main/vendor/libs/jquery/jquery.js')}}"></script>
<script src="{{asset('main/vendor/js/bootstrap.js')}}"></script>
<script src="{{asset('main/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>
<script src="{{asset('main/vendor/js/menu.js')}}"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{asset('main/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<!-- Main JS -->
<script src="{{asset('js/main.js')}}"></script>

<script src="{{asset('js/alerts.min.js')}}"></script>

<style>
    .back-to-top {
        display: none !important;
        color: #30334e;
        background-color: rgba(231, 231, 255, 0.8);
        -webkit-backdrop-filter: saturate(180%) blur(6px);
        backdrop-filter: saturate(180%) blur(6px);
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: auto;
        transition: opacity 0.3s ease-in-out !important;
    }

    .dark-style .back-to-top {
        color: #e7e7ff;
        background-color: rgba(48, 51, 78, .8)
    }

</style>
<!-- Page JS -->
@yield('script')

@if(session()->has('alert'))
    <script>
        {!! session('alert') !!}
    </script>
@endif

@if (session('error'))
    <script>
        console.log('error');
        errorAlert('{{ session('error') }}');
    </script>
@endif
<script>
    function updateClock() {
        let now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();

        document.getElementById('clock').innerHTML = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    }

    updateClock();
    setInterval(updateClock, 1000);

    document.addEventListener('DOMContentLoaded', function () {
        let backToTopButton = document.getElementById('backToTopBtn');

        window.addEventListener('scroll', function () {
            if (window.scrollY > 200) {
                backToTopButton.style.setProperty('display', 'block', 'important');
            } else {
                backToTopButton.style.setProperty('display', 'none', 'important');
            }
        });

        backToTopButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    })
</script>
</body>
</html>
