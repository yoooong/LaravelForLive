<!doctype html>
<html class="no-js" lang="">

<head>
    <meta charset="utf-8">
    <title>lpsp管理后台</title>
    <meta name="description" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/vendor/sweetalert/dist/sweetalert.css">
    <!-- page stylesheets -->
    <link rel="stylesheet" href="/vendor/datatables/media/css/datatables.bootstrap.css">
    <!-- end page stylesheets -->
    <!-- build:css({.tmp,app}) styles/app.min.css -->
    <link rel="stylesheet" href="/styles/webfont.css">
    <link rel="stylesheet" href="/styles/climacons-font.css">
    <link rel="stylesheet" href="/vendor/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="/styles/font-awesome.css">
    <link rel="stylesheet" href="/styles/card.css">
    <link rel="stylesheet" href="/styles/sli.css">
    <link rel="stylesheet" href="/styles/animate.css">
    <link rel="stylesheet" href="/styles/app.css">
    <link rel="stylesheet" href="/styles/app.skins.css">
    <!-- endbuild -->
@yield('css')
</head>
<bady class="page-loading">
    {{--@section('sidebar')--}}

    {{--@show--}}
    @include('sidebar')

    {{--@yield('content')--}}


    <script src="/scripts/helpers/modernizr.js"></script>
    <script src="/vendor/jquery/dist/jquery.js"></script>
    <script src="/vendor/bootstrap/dist/js/bootstrap.js"></script>
    <script src="/vendor/fastclick/lib/fastclick.js"></script>
    <script src="/vendor/perfect-scrollbar/js/perfect-scrollbar.jquery.js"></script>
    <script src="/scripts/helpers/smartresize.js"></script>
    <script src="/scripts/constants.js"></script>
    <script src="/scripts/main.js"></script>
    <script src="/vendor/noty/js/noty/packaged/jquery.noty.packaged.min.js"></script>
    <script src="/scripts/helpers/noty-defaults.js"></script>
    <script src="/vendor/sweetalert/dist/sweetalert.min.js"></script>
    {{--<script src="https://unpkg.com/vue/dist/vue.js"></script>--}}
    @yield('javascript')
</bady>

</html>