@extends('layouts.master')

@section('content')
<div class="row">
    <div class="card">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center breaking-news bg-white">
            <div class="d-flex flex-row flex-grow-1 flex-fill justify-content-center bg-danger py-2 text-white px-1 news"><span class="d-flex align-items-center"><i class="fas fa-envelope"></i></span></div>
            <marquee class="news-scroll" behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();"> 
              <a href="#">--[ Login as User ]-- </a> <span class="dot"></span> 
              <a href="https://agile.co.id">Agile Sustainability Report and Information  [ASRI] </a> <span class="dot"></span> 
              <a href="https://agile.co.id">for futher information please visit https://agile.co.id</a> </marquee>
        </div>
    </div>
    </div>
</div>
<!DOCTYPE html>
<html>
<head>
@endsection
