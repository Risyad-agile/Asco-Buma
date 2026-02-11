@extends('layouts.master')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Admin Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @role('admin')
                      <p>This is visible to users with the admin role. Gets translated to 
                         \Entrust::role('admin')</p>
                    @endrole
                    <p>Halaman Admin ini akan menampilkan Dashboard terkait dengan aktifitas admin, seperti
                    penambahan user dalam satu bulan, penambahan master data, total dari master data yang ada
                    dan sebagainya</p>
                </div>
            </div>
        </div>
    </div>
</div>


<!DOCTYPE html>
<html>
<head>



   

@endsection
