@extends('layouts.master')

@section('content')
<div class="row">
    <div class="card">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center breaking-news bg-white">
            <div class="d-flex flex-row flex-grow-1 flex-fill justify-content-center bg-danger py-2 text-white px-1 news"><span class="d-flex align-items-center"><i class="fas fa-envelope"></i></span></div>
            <marquee class="news-scroll" behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();"> 
              <a href="#">--[ Login as Manager ]-- </a> <span class="dot"></span> 
              <a href="https://agile.co.id">Agile Sustainability Report and Information  [ASRI] </a> <span class="dot"></span> 
              <a href="https://agile.co.id">for futher information please visit https://agile.co.id</a> </marquee>
        </div>
    </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4 col-6">
      <!-- small box -->
      <div class="small-box bg-info">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_account_styles)!!}</h4></div>
            <p>Account Styles</p>
          </div>
          <div class="icon">
            <i class="fa fa-cubes"></i>
          </div>
        <a href="{{route('accountstyles.company.index')}}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-4 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_locations)!!}</h4></div>
            <p>Locations</p>
          </div>
          <div class="icon">
            <i class="fas fa-map-marker-alt"></i>
          </div>
        <a href="#" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div> 
    <div class="col-lg-4 col-6">
      <!-- small box -->
      <div class="small-box bg-warning">
        <div class="inner">
          <!-- <div class="long-title"><h4>{!!number_format($dashboard->dash_task_total)!!}<sup style="font-size: 20px">%</sup></h4></div> -->
          <div class="long-title"><h4>{!!number_format($dashboard->dash_task_total)!!}</h4></div>
            <p>Total Tasks</p>
          </div>
          <div class="icon">
            <i class="fas fa-tasks"></i>
          </div>
        <a href="#" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
  </div>
<!DOCTYPE html>
<html>
<head>
@endsection
