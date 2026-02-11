@extends('layouts.master')
@section('content')
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
        <span><b>Pilih pada produk untuk unggah gambar baru</b></span>
    </div>  
    {{-- add products dialog --}}
    <div class="modal fade" id="mdlPicLoc" role="dialog">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Gambar</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="container">
                    <form id="form" method="post" action="{{url('office/products/image/save')}}"  enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <h3>Gambar Produk</h3>
                        <div class="dx-fieldset">
                            <div class="dx-field">
                                <div class="dx-field-label">ID</div>
                                <div class="dx-field-value" id="productid"></div>
                            </div>
                            <div class="dx-field">
                                <div class="dx-field-label">Produk</div>
                                <div class="dx-field-value" id="productdesc"></div>
                            </div>
                        </div>
                        <div id="fileuploader-container">
                            <button type="button" class="btn btn-success" onclick="document.getElementById('image').click();" >Pilih Gambar untuk di Unggah</button>
                            <input type="file" accept="image/*"  style="display:none;"  name="image" id="image">
                            <div class="form-group">
                                <div class="form-group row">
                                    <input id="txtfilename" type="text" class="form-control" placeholder="Nama File" readonly>
                                    <input id="txtfilesize" type="text" class="form-control" placeholder="Ukuran File" readonly>
                                    <input id="txtfiletype" type="text" class="form-control" placeholder="Jenis File" readonly>
                                    <input id="txtfilemodif" type="text" class="form-control" placeholder="Modifikasi Terakhir" readonly>
                                </div>
                            </div>

                            {{-- <input type="file" accept="image/*"  name="image" id="image"> --}}
                            {{-- <button type="submit" class="btn btn-primary" form="form" value="Submit">Submit</button> --}}
                            {{-- <button type="button" class="btn btn-primary" onclick="document.getElementById('btnsubmin').click();" >Pilih Unggah Gambar</button> --}}
                            {{-- <div id="button"></div> --}}
                        </div>
                    </form>
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
                return $("<div class='long-title'><h3>Pengaturan Gambar Produk</h3></div>");
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
        if(this.files[0].size > 128000){
            filesize=filesize+ " [File is too big...!!!]"
        };

        $("#txtfilename").val("Nama   : " +this.files[0].name);
        $("#txtfilesize").val("Ukuran : " +filesize);
        $("#txtfiletype").val("Jenis    : " +this.files[0].type);
        $("#txtfilemodif").val("Modif  : " +this.files[0].lastModifiedDate);
    };

     $("#productid").dxTextBox({
        value: "",
        name: "productid"
    });
    
    $("#productdesc").dxTextBox({
    value: "",
    name: "productdesc"
    });
    $("#button").dxButton({
        text: "Update Gambar",
        type: "success",
        onClick: function(){
            $("#form").submit();
        }
    });     

    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('products.create')}}"
          })
      },
    });

  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,//prods,
        // keyExpr: "product_id",
        showBorders: true,
        rowAlternationEnabled: true,
        height:500,
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
                dataField: "product_file_loc",
                caption:"Photo",
                // width: 150,
                allowFiltering: false,
                allowSorting: false,
                cellTemplate: function (container, options) {
                    $("<div>")
                        .append($("<img>", { "src": options.value }))
                        .appendTo(container);
                }
            },{ 
                dataField: "product_id",
                caption: "ID Produk",
                value:"[AUTO NUMBER]",
                visible:false,
                // width:150,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "productcats.prodcat_desc",
                caption: "Kategori Produk",
                // width:200,
            },{
                dataField: "brand_id",
                caption: "Merek",
                visible:false,
                // width:175,
            },{
                dataField: "product_barcode",
                caption: "Barcode",
                visible:false,
                // width:125,
            },{
                dataField: "product_desc",
                caption: "Deskripsi",      

            },{
                dataField: "product_stockstate",
                caption: "Status Stok",
                visible:false,
                // width:125,
                lookup: {
                    dataSource: [{"product_stockstate":"1","product_stockstate_desc":"Stock"},
                            {"product_stockstate":"0","product_stockstate_desc":"Non Stock"}],
                    valueExpr: "product_stockstate",
                    displayExpr: "product_stockstate_desc",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            },

        ],
        onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            $('#mdlPicLoc').modal('show');            
            if(data) {
                // console.log(data.product_id);
                $("#productid").data("dxTextBox").option("value",data.product_id);
                $("#productdesc").data("dxTextBox").option("value",data.product_desc);
            }
        },
        onEditingStart: function(e){
        },
    });
});
</script>
@endsection
