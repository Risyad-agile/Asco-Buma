@extends('layouts.master')
@section('content')
<div class="row justify-content-center" >
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div class="card-header"><div id="toolbar"></div></div>
            <div class="card-body">
                <label>{{$message}}</label>
                <div id="btnLanjut"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Kidswa Farma</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
                onClick: function(e) {      
                    window.location = "{{route('home')}}";
                }
            }
        }]
    });


    $("#btnLanjut").dxButton({
        type: "danger",
        text: "Tutup",
        useSubmitBehavior: true,
        onClick: function(e) {      
            window.location = "{{route('home')}}";
        }
    });
 
});
</script>
@endsection


