<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Zaida Ritel Solution</title>
    {{-- <title>{{ config('app.name', ' 00 Shop') }}</title> --}}


    <!-- Fonts -->
    {{-- <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css"> --}}

    <link rel="stylesheet" href="{{asset('/css/dx.common.css')}}"  type="text/css">
    <link rel="stylesheet" href="{{asset('/css/dx.greenmist.css')}}"  type="text/css">
    <link rel="stylesheet" href="{{asset('css/jquery-ui.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('css/datepicker3.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('css/jquery.dataTables.min.css')}}" type="text/css">


    <!-- Scripts -->
    {{-- <script src="{{ asset('js/app.js') }}"></script> --}}
    <script src="{{ asset('js/jquery.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/bootstrap.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/datepicker/bootstrap-datepicker.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/jquery.dataTables.js')}}" type="text/javascript" charset="utf8"></script>
    <script src="{{ asset('js/jszip/dist/jszip.min.js')}}" type="text/javascript"></script>
    <script src="{{ asset('js/dx.all.js')}}" type="text/javascript"></script>
    <script>window.jQuery || document.write(decodeURIComponent('%3Cscript src="js/jquery.min.js"%3E%3C/script%3E'))</script>



    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    <style>
      .long-title h3 {
        font-family: 'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana;
        font-weight: 200;
        font-size: 28px;
        text-align: center;
        margin-bottom: 20px;
      }
      /* Remove the navbar's default margin-bottom and rounded borders */
      .navbar {
        margin-bottom: 0;
        border-radius: 0;
      }

      /* Set height of the grid so .sidenav can be 100% (adjust as needed) */
      .row.content {height: 450px}

      /* Set gray background color and 100% height */
      .sidenav {
        padding-top: 20px;
        background-color: #f1f1f1;
        height: 100%;
      }

      /* Set black background color, white text and some padding */
      footer {
        background-color: #555;
        color: white;
        padding: 15px;
      }
      .content {
          /* max-width: 500px; */
          margin: auto;
          padding: 10px;
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
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
  <a class="navbar-brand" href="{{ route('login') }}">Zaida Online</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarCollapse">
  @guest
  <ul class="navbar-nav mr-auto">
  </ul>
  <ul class="navbar-nav px-3">
    <li class="nav-item active">
        <a class="nav-link" href="#">About <span class="sr-only">(current)</span></a>
    </li>
  </ul>
  @else
    <ul class="navbar-nav mr-auto">
    </ul>
    <ul class="navbar-nav px-3">
    <li class="nav-item text-nowrap">
      <a class="nav-link" href="{{ route('logout')}}" onclick="event.preventDefault();
                          document.getElementById('logout-form').submit();">Sign out</a>
    </li>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
               @csrf
    </form>
  </ul>
  @endguest
  </div>
</nav>
</div>
<main role="main" class="flex-shrink-0">
  <div class="container">
  <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pesan Sistem</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    Jika ada melihat halaman ini, berarti user anda belum memiliki Lokasi Toko
                    Silakan Hubungi Admin Aplikasi untuk dilakukan pengaturan

                    
                </div>
            </div>
        </div>
    </div>
</div>



  </div>
</main>


<nav class="navbar fixed-bottom navbar-expand-sm navbar-dark bg-dark">
  <span class="text-muted"></span>
  <ul class="navbar-nav mr-auto">
  </ul>
  <ul class="navbar-nav px-3">
    <li class="nav-item active">
    <span class="text-muted">Zaida Ritel Solution @2019</span>
    </li>
  </ul>
</nav>



<!-- mencegah injection -->
<!-- </body></html> -->
</body>




   

