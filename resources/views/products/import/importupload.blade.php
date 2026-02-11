@extends('layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Upload File XLS Daftar Produk</div>
                <div class="card-body">
                    <div id="file-uploader"></div>
                    <div class="content" id="selected-files">
                        <div>
                            <h5>Informasi File</h5>
                        </div>
                    </div>
                    <div id="upload-progress"></div>
                    <form id="form-container" class="first-group" method="POST" action="{{route('product.import.brand')}}">
                        @csrf
                        <div id="btnLanjut"></div> 
                        <input id="txtCompId" type="text" name="compid" class="form-control" placeholder="ID" value="{!!$compid!!}" hidden>
                        <input id="txttype" type="text" name="type" class="form-control" value="{!!$type!!}" placeholder="Type" hidden>
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
   
    const storeorigin="{!!$storeorigin!!}";
    const storedestination="{!!$storedestination!!}";
    const compid="{!!$compid!!}";
    const type="{!!$type!!}";
    var fileUploader = $("#file-uploader").dxFileUploader({
        uploadHeaders: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},  
        multiple: false,
        accept: "*",
        uploadedMessage:"Upload File ke Server BERHASIL",
        uploadFailedMessage: "Upload File ke Server GAGAL !!!",
        uploadMode: "instantly",
        uploadUrl:  "{{route('product.import.upload')}}",
        maxFileSize: 250000,
        uploadCustomData:{compid,storeorigin,storedestination,type},
        onUploaded(e){
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
        text: "Lanjut",
        disabled:true,
        useSubmitBehavior: true,
        onClick: function(e) {      
           // var txtstores=document.getElementById("txtstoreid").value;
           $("#txtCompId").val(compid); 

      }
    }).dxButton("instance");;
});
</script>
@endsection