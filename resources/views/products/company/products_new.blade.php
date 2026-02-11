@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('products.company.update.main')}}">
    @csrf 
    <div id="toolbar"></div>
    <input id="txtProductId" type="text" name="product_id" class="form-control" hidden>
    <input id="txtStatus" type="text" name="state" class="form-control" hidden>
</form>
<div id="toolbar"></div>
<div id="gridContainer" style="margin-top: 25px;"></div> 
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
                return $("<div class='long-title'><h3>Pembaharuan Produk</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "edit",
                    hint: 'Update Produk',
                    useSubmitBehavior: true,
                    onClick: function(e) {      
                        var txtProductId=document.getElementById("txtProductId").value;
                        if(txtProductId==""){
                            DevExpress.ui.notify({
                                message: "Silakan Pilih Produk..",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                    }
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

    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('products.company.load')}}"
          })
      },
  });


  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,//prods, 
        showBorders: true,
        allowColumnResizing: true,
        keyExpr: "id",
        selection: {
            mode: "single"
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                dataField: "id",
                caption: "ID Produk",
                value:"[AUTO NUMBER]",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "productcategory.prodcat_desc",
                caption: "Kategori Produk",
            },{
                dataField: "brand.brand_name",
                caption: "Merek",
                visible:false,
            },{
                dataField: "productunit.unit_name",
                caption: "Satuan",   
            },{
                dataField: "product_barcode",
                caption: "Barcode",
                visible:false,
            },{
                dataField: "product_name",
                caption: "Nama",     
                validationRules: [{
                    type: "required",
                    message: "Silakan di isi deskripsi produk...",
                },], 
            },{
                dataField: "product_dot",
                caption: "DOT",
                // width:125,
                visible:false,
                lookup: {
                    dataSource: [{"product_unit":"HIJAU"},{"product_unit":"BIRU"},
                            {"product_unit":"MERAH"},{"product_unit":"NO DOT"}],
                    valueExpr: "product_unit",
                    displayExpr: "product_unit",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            },{
                dataField: "product_stock_state",
                caption: "Status Stok",
                // width:125,
                visible:false,
                lookup: {
                    dataSource: [{"product_stock_state":"1","product_stock_state_desc":"Stock"},
                            {"product_stock_state":"0","product_stock_state_desc":"Non Stock"}],
                    valueExpr: "product_stock_state",
                    displayExpr: "product_stock_state_desc",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            },

        ],
        onSelectionChanged: function (selectedItems) {
            var data = selectedItems.selectedRowsData[0];
            $("#txtProductId").val(data.id);
            $("#txtStatus").val("UPDATE");
        },        
    });
});
</script>
@endsection
