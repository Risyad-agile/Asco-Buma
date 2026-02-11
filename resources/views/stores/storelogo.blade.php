@extends('layouts.master')
@section('content')
<form id="form" method="post" action="{{route('stores.logo.update')}}"  enctype="multipart/form-data">
<div class="content">
    <div id="toolbar"></div>
    @if($message = Session::get('error'))
        <div class="alert alert-danger">
            <strong>{{$message}}</strong>
        </div>
    @endif
    @if($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <strong>{{$message}}</strong>
        </div>
    @endif
    <div id="gridContainer"></div> 
    <input id="txtstoreid" type="text" name="storeid" class="form-control" placeholder="Store ID" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" placeholder="Store ID" hidden>
    {{-- <span><b>Pilih pada Toko untuk unggah logo struk baru</b></span> --}}
</div>
{{-- add products dialog --}}
<div class="modal fade" id="mdlPicLoc" role="dialog">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Update Logo</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="container">
                    {{ csrf_field() }}
                    <h3>Logo Toko/Resto</h3>
                    <div class="dx-fieldset">
                        <div class="dx-field">
                            <div class="dx-field-label">ID</div>
                            <div class="dx-field-value" id="storeid"></div>
                        </div>
                        <div class="dx-field">
                            <div class="dx-field-label">Toko/ Resto</div>
                            <div class="dx-field-value" id="storename"></div>
                        </div>
                    </div>
                   
                    <div id="fileuploader-container">
                        <button type="button" class="btn btn-success" onclick="document.getElementById('image').click();" >Pilih Logo untuk di Unggah</button>
                        <input type="file" accept="image/*"  style="display:none;"  name="image" id="image">
                        <div class="form-group">
                            <div class="form-group row">
                                <input id="txtfilename" type="text" class="form-control" placeholder="Nama File" readonly>
                                <input id="txtfilesize" type="text" class="form-control" placeholder="Ukuran File" readonly>
                                <input id="txtfiletype" type="text" class="form-control" placeholder="Jenis File" readonly>
                                <input id="txtfilemodif" type="text" class="form-control" placeholder="Modifikasi Terakhir" readonly>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" form="form" value="Submit">Submit</button>
          </div>
    </div>
    </div>
</div>
{{-- end of add Product dialog --}}   
</form>
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
            return $("<div class='long-title'><h3>Pengaturan Logo Struk Apotik</h3></div>");
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
    var uploadFile = document.getElementById("image");
    uploadFile.onchange = function() {
        var filesize=new Intl.NumberFormat('id-ID', { maximumSignificantDigits: 3 }).format(this.files[0].size)+" bytes";
        if(this.files[0].size > 16000){
            filesize=filesize+ " [File is too big, max 16 Kb]"
        };
        $("#txtfilename").val("Nama   : " +this.files[0].name);
        $("#txtfilesize").val("Ukuran : " +filesize);
        $("#txtfiletype").val("Jenis    : " +this.files[0].type);
        $("#txtfilemodif").val("Modif  : " +this.files[0].lastModifiedDate);
    };

    $("#storeid").dxTextBox({
        value: "",
        name: "storeid"
    });
    
    $("#storename").dxTextBox({
        value: "",
        name: "storename"
    });

    var gridDataSource = new DevExpress.data.DataSource({
        load: function (key) {
            return $.ajax({
                url: "{{route('stores.company.load')}}"
            })
        },
        update: function (key, values) {
            var kunci= key.id;
            return $.ajax({
                url: "{{URL::to('farma/stores')}}"+"/"+kunci,
                method: "PUT",
                data: values
            })
        }
    });

  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        // keyExpr: "store_id",
        showBorders: true,
        searchPanel: {
            visible: true
        },
        selection: {
            mode: "single"
        },
        scrolling: {
            mode: 'infinite'
        },
        columns: [
            {
                dataField: "store_logo_loc",
                caption:"Logo",
                allowFiltering: false,
                allowSorting: false,
                cellTemplate: function (container, options) {
                    $("<div>")
                        .append($("<img>", { "src": options.value }))
                        .appendTo(container);
                }
            },{ 
                dataField: "id",
                caption: "ID Toko",
                value:"[AUTO NUMBER]",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "store_name",
                caption: "Nama Toko",
            },{
                dataField: "store_struck_anote",
                caption: "Struk Note 1",  
                validationRules: [{
                    type: "stringLength",
                    min:0, 
                    max:30,
                    message: "Panjang  Note 1, 36 Digit..."}]
            },{
                dataField: "store_struck_bnote",
                caption: "Struk Note 2",  
                validationRules: [{
                    type: "stringLength",
                    min:0, 
                    max:30,
                    message: "Panjang  Note 2, 36 Digit..."}]
            },
        ],
        toolbar: {
        items: [
            'searchPanel',
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'image',
                    hint: 'Ganti Logo',
                    onClick() {
                        var txtstoreid=document.getElementById("txtstoreid").value;
                        if(txtstoreid==""){
                            DevExpress.ui.notify({
                                message: "Silakan pilih Toko...",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                        $('#mdlPicLoc').modal('show'); 
                        $("#txtstate").val("UPDATE"); 
                    },
                },
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'trash',
                    hint: 'Reset Logo',
                    useSubmitBehavior:true,
                    onClick() {
                        var txtstoreid=document.getElementById("txtstoreid").value;
                        if(txtstoreid==""){
                            DevExpress.ui.notify({
                                message: "Silakan pilih Toko...",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                        $("#txtstate").val("RESET"); 
                    },
                },
            }
        ],},    
        onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            if(data) { 
                $("#storeid").data("dxTextBox").option("value",data.id);
                $("#storename").data("dxTextBox").option("value",data.store_name);
                $("#txtstoreid").val(data.id); 
            }
        },
    });
});
</script>
@endsection
