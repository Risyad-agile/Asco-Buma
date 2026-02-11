@extends('layouts.master')
@section('content')
<div class="leftmenu">

    <nav class="nav flex-column">
        {{-- <a class="nav-link text-white bg-primary active" href="{{route('dashboard.manager.index')}}">Dashboard Overview</a>
        <a class="nav-link text-white bg-primary" href="{{route('dashboard.manager.sales')}}">Sales</a>
        <a class="nav-link text-white bg-primary" href="{{route('dashboard.manager.products')}}">Product</a>
        <a class="nav-link text-white bg-primary" href="{{route('dashboard.manager.stores')}}">Store</a> --}}
        <!-- <button type="button" class="btn btn-labeled btn-success">
                <span class="btn-label"><i class="glyphicon glyphicon-ok"></i></span>Success
        </button>
        <button type="button" class="btn btn-success"><span class="cui-contrast"></span> Success Button</button>
        <button type="button" class="btn btn-brand btn-google-plus">
        <i class="fa fa-google-plus"></i>
        </button> -->
    </nav>

</div>


<div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 text-dark"><span class="brand-text font-weight-light">Dashboard</span></h1>
        </div> 
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </div> 
      </div> 
    </div> 
</div>

  
  <section class="content">
    <div class="container-fluid">
        @yield('manager')
    </div> 
  </section>
 
@endsection


@section('script')
 @yield('managerscript')
@endsection