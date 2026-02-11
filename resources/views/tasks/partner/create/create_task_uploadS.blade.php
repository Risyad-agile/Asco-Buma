@extends('layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8" style="margin-top: 25px;">
            <div class="card">
                <div id="toolbar"></div>
                {{-- <div class="card-header">Upload Account Data Load XLS File</div> --}}
                <div class="card-body">
                    <div id="file-uploader"></div>
                    <div class="content" id="selected-files">
                        <div>
                            <h5>File Information</h5>
                        </div>
                    </div>
                    <div id="upload-progress"></div>
                    <form id="form-container" class="first-group" method="POST" action="{{route('create.task.crud.newUploadS')}}">
                        @csrf
                        <div id="btnLanjut"></div>  
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
                return $("<div class='card-header'>Upload Account Data Load XLS File</div>");
            }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Close',
                // useSubmitBehavior: true,
                onClick: function(e) {   
                    window.location = "{{route('create.task.listS')}}";   
                }
            }
        }]
    });
    var fileUploader = $("#file-uploader").dxFileUploader({
        uploadHeaders: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        multiple: false,
        accept: "*",
        uploadedMessage:"File SUCCESSFULL Uploaded to Server",
        uploadFailedMessage: "File FAILED Uploaded to Server !!!",
        uploadMode: "instantly",
        uploadUrl:  "{{route('create.task.crud.newUploadS')}}",
        maxFileSize: 250000, 
        onUploaded(e){
            console.log(e);

            fileUploader.option("disabled",false);  
            btnContinue.option("disabled",false);  
            uploadProgressBar.option({
                visible: false,
                value: 0,
            });
        },
        onValueChanged: function(e) {
            var files = e.value;
            fileUploader.option("disabled",true);  
            btnContinue.option("disabled",false);  
            if(files.length > 0) {
                $("#selected-files .selected-item").remove();
                $.each(files, function(i, file) {
                    var $selectedItem = $("<div />").addClass("selected-item");
                    $selectedItem.append(
                        $("<span />").html("Nama File :" + file.name + "<br/>"),
                        $("<span />").html("Ukuran    :" + file.size + " bytes" + "<br/>"),
                        $("<span />").html("Jenis     :" + file.type + "<br/>"),
                        $("<span />").html("Pembaharuan Terakhir :" + file.lastModifiedDate)
                    );
                    $selectedItem.appendTo($("#selected-files"));
                });
                $("#selected-files").show();
            }
        },
        onProgress(e) {
            uploadProgressBar.option('value', (e.bytesLoaded / e.bytesTotal) * 100);
        },
        onUploadStarted() {
            toggleImageVisible(false);
            uploadProgressBar.option('visible', true);
        },
        onFilesUploaded: function(e){
            console.log(e);
        },
        onUploadError: function(e){
            var xhttp = e.request;
            console.log(e);
            if (xhttp.readyState == 4 && xhttp.status == 0) {
                console.log("Connection refused.");
            }
        }
    }).dxFileUploader("instance");
    const uploadProgressBar = $('#upload-progress').dxProgressBar({
        min: 0,
        max: 100,
        width: '30%',
        showStatus: false,
        visible: false,
    }).dxProgressBar('instance');

    var btnContinue=$("#btnLanjut").dxButton({
        type: "success",
        text: "Continue",
        disabled:true,
        onClick: function(e) {
            window.location = "{{route('create.task.listS')}}"; 
        }
    }).dxButton("instance");;
});
</script>
@endsection