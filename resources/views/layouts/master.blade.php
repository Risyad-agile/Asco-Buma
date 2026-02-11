<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, shrink-to-fit=no" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ASRI-Connect') }}</title>
    {{-- <!-- <title>{{ config('app.name', 'Agile') }}</title> --> --}} 
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('images/agile_logo.png')}}">
    <!-- Fonts -->
    <link rel="stylesheet" href="{{asset('/plugins/fonts/nurito.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('/plugins/fonts/opensanspro.css')}}" type="text/css"> 

    <link rel="stylesheet" href="{{asset('/plugins/devex/css/dx.common.css')}}"  type="text/css">
    <link rel="stylesheet" href="{{asset('/plugins/devex//css/dx.light.css')}}"  type="text/css">
    <link rel="stylesheet" href="{{asset('/plugins/devex//css/dx.greenmist.css')}}"  type="text/css">
    <link rel="stylesheet" href="{{asset('/plugins/bootstrap5/css/bootstrap.min.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('/plugins/datepicker/datepicker3.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('/plugins/jquery/css/jquery.dataTables.min.css')}}" type="text/css"> 


    <link rel="stylesheet" href="{{asset('/plugins/fontawesome/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('/plugins/sweetalert2/css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <link rel="stylesheet" href="{{asset('/plugins/adminlte/css/adminlte.min.css')}}">
  

    <!-- Scripts --> 
    <script src="{{ asset('plugins/popper/popper.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/popper/popper.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/jszip/jszip.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/jquery/jquery.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/bootstrap5/js/bootstrap.bundle.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/chart.js/dist/Chart.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/adminlte/js/adminlte.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/sweetalert2/js/sweetalert2.min.js')}}"></script>
    <script src="{{ asset('plugins/sweetalert2/js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('plugins/datepicker/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/devex/js/dx.all.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/jspdf/jspdf.umd.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/jspdf/jspdf.plugin.autotable.js')}}" type="text/javascript"></script>
    <script src="{{ asset('plugins/exceljs/polyfill.min.js')}}"></script>
    <script src="{{ asset('plugins/exceljs/exceljs.js')}}"></script>
    {{-- <script src="{{ asset('plugins/exceljs/exceljs.min.js')}}"></script> --}}
    <script src="{{ asset('plugins/exceljs/FileSaver.min.js')}}"></script>
    
    <script>window.jQuery || document.write(decodeURIComponent('%3Cscript src="js/jquery.min.js"%3E%3C/script%3E'))</script>
    <style>
      .long-title h3 {
        font-family: 'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana;
        font-weight: 250;
        font-size: 28px;
        text-align: center;
        margin-bottom: 10px;
      }
      .short-title h4 {
        font-family: 'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana;
        font-weight: 150;
        font-size: 20px;
        text-align: center;
        margin-bottom: 10px;
      }
      .content {
          /* max-width: 500px; */
          margin: auto;
          /* padding: 2px; */
          margin-left:5px;
          margin-right:5px;
      }
      .container {
          /* max-width: 1200px;  */
          margin: auto;
          padding: 5px;
          margin-left:100px;
          margin-right:5px;
      }
      .blinking{
        animation:blinkingText 1.2s infinite;
      }
      @keyframes blinkingText{
          0%{     color: #000;    }
          49%{    color: #000; }
          60%{    color: transparent; }
          99%{    color:transparent;  }
          100%{   color: #000;    }
      }

      .contentwithleftmenu{
          margin: auto;
          padding: 5px;
          margin-left:10px;
          margin-right:5px;
          /* max-width: 700px; */
      }
      /* #chart {
          height: 450px;
      } */
      img {
          height: 100px;
          width: 100px;
          display: block;
      }
      /* On small screens, set height to 'auto' for sidenav and grid */
      @media screen and (max-width: 767px) {
        .sidenav {
          height: auto;
          padding: 15px;
        }
        .row.content {height:auto;}
      }
    </style>
    <!-- </head> -->
    <link rel="shortcut icon" href="">  <!-- hadle faveico error -->
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">
      <!-- Header -->
      @include('layouts.header')
      
      <!-- Sidebar -->
      @include('layouts.sidebar')

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <section class="content">
          <div class="container-fluid">
            @yield('content')
          </div>
        </section>
      </div>
      <!-- /.content-wrapper -->

       <!-- Footer -->
      @include('layouts.footer')

    </div>
    <!-- ./wrapper -->
  
  
  
  <!-- mencegah injection -->
  <!-- </body></html> -->
  </body>
  @yield('script')
  <script>


$(function () {
    var url = window.location;
      // for single sidebar menu
      $('ul.nav-sidebar a').filter(function () {
          return this.href == url;
      }).addClass('active');

      // for sidebar menu and treeview
      $('ul.nav-treeview a').filter(function () {
          return this.href == url;
      }).parentsUntil(".nav-sidebar > .nav-treeview")
          .css({'display': 'block'})
          .addClass('menu-open').prev('a')
          .addClass('active');
  });
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
  
    
  });
  </script>
  </html>