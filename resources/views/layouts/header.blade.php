<header class="main-header">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-green navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('login') }}" data-toggle="tooltip" data-placement="bottom" title="Home">
              <i class="fas fa-home"></i>
                    {{-- <img src="{{asset('images/ic_home_white_24dp.png')}}"
                         class="media-object" style="width:20px;height:20px"></i> --}}
                    <span class="sr-only">(current)</span>
            </a>
          </li> 

          {{-- @hasanyrole('superadmin|manager') 
          <li class="nav-item">
            <a href="{{ route('dashboard.stores.index') }}" class="nav-link" data-toggle="tooltip" data-placement="bottom" title="Dashboard Store">
              <i class="fas fa-store"></i></a> 
          </li>
          @endrole --}}
        </ul>
  
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="{{ route('logout')}}"
                      data-toggle="tooltip" data-placement="left" title="Sign Out"
                      onclick="event.preventDefault();
                      document.getElementById('logout-form').submit();">
                      <i class="nav-icon fas fa-sign-out-alt"></i> 
            </a>
          </li>
        </ul>
      </nav>
      
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
             @csrf
      </form>
      <!-- /.navbar -->
</header>