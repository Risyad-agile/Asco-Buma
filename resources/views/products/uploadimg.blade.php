@extends('layouts.master')
@section('content')
    <div class="content">
        <div class="long-title"><h3>Upload Produk</h3></div>
        @if(count($errors) > 0)
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
            {{ $error }} <br/>
            @endforeach
        </div>
        @endif
        @if($message = Session::get('success'))
        <div class="aler aler-success alert-block">
        <strong>{{$message}}</strong>
        </div>
        @endif
    <img src="/demo/public/images/{{Session::get('path')}}" width="200">

        <form id="form" method="post" action="{{url('/agile/product/image/save')}}" enctype="multipart/form-data">
            {{ csrf_field() }}
 
            <div class="form-group">
                <b>File Gambar</b><br/>
                <input type="file" name="file-uploader">
            </div>

            <div class="form-group">
                <b>Keterangan</b>
                <textarea class="form-control" name="keterangan"></textarea>
            </div>

            <input type="submit" value="Upload" class="btn btn-primary">

            {{-- <h3>Profile Settings</h3>
            <div class="dx-fieldset">
                <div class="dx-field">
                    <div class="dx-field-label">First Name:</div>
                    <div class="dx-field-value" id="first-name"></div>
                </div>
                <div class="dx-field">
                    <div class="dx-field-label">Last Name:</div>
                    <div class="dx-field-value" id="last-name"></div>
                </div>
            </div>
            <div id="fileuploader-container">
                <div id="file-uploader"></div>
            </div>
            <div id="button"></div> --}}
        </form>
    </div>  
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $("#first-name").dxTextBox({
        value: "Agile",
        name: "FirstName"
    });
    
    $("#last-name").dxTextBox({
        value: "Ritel",
        name: "LastName"
    });
    
    $("#file-uploader").dxFileUploader({
        selectButtonText: "Select photo",
        labelText: "",
        accept: "image/*",
        uploadMode: "useForm"
    });
    
    $("#button").dxButton({
        text: "Update profile",
        type: "success",
        onClick: function(){
            // DevExpress.ui.dialog.alert("Uncomment the line to enable sending a form to the server.", "Click Handler");
            $("#form").submit();
        }
    });
});
</script>
@endsection