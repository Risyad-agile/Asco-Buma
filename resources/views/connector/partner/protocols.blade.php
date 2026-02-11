@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div id="toolbar"></div>
            <div class="card-body">
                <form method="POST" action="{{route('partner.connector.new.protocol')}}">
                    @csrf 
                    <label>Choose Protocol From List</label>
                    <div id="radioProtocols"></div>
                    <div id="btnLanjut"></div>
                    <input id="txtcompid" type="text" name="compid" value="{!!$company->id!!}" 
                            class="form-control" hidden>
                    <input id="txtvalue" type="text" name="protocol" value="API" 
                            class="form-control" hidden>
                </form>
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
            return $("<div class='long-title'><h3>Please Choose Protocol</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
                // useSubmitBehavior: true,
                onClick: function(e) {      
                    window.location = "{{route('home')}}";
                }
            }
        }]
    });

    const priorities = ['API','FTP'];
    $('#radioProtocols').dxRadioGroup({
        items: priorities,
        value: priorities[0],
        onValueChanged(e) {
            $("#txtvalue").val(e.value);  
        },
    });
 
    $("#btnLanjut").dxButton({
        type: "success",
        text: "Lanjut",
        useSubmitBehavior: true,
    });
 
});
</script>
@endsection


