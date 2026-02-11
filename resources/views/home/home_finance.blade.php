@extends('layouts.master')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    You are logged in! ou are logged in as Finance! 
                    @role('finance')
                      <p>This is visible to users with the admin role. Gets translated to 
                         \Entrust::role('finance')</p>
                    @endrole
                    <p>Halaman Manager ini akan menampilkan segala sesuatu terkait dengan keuangan perusahaan
                    seperti rugi laba, penjualan harian, persediaan barang dan sebagainya</p>


                </div>
            </div>
        </div>
    </div>
</div>


<!DOCTYPE html>
<html>
<head>



   

@endsection
