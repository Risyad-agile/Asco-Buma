@extends('layouts.master')
@section('content')
    <div id="toolbar"></div>
    <div id="gridProduk"></div>
@endsection

@section('script')
<script type="text/javascript">
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
            return $("<div class='long-title'><h3>Daftar Stok Kurang dari PKM {!! $store->store_name !!}</h3></div>");
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
$(function(){
    $("#gridProduk").dxDataGrid({
        dataSource: {!! $products !!},
        showBorders: true,
        allowColumnResizing: true,
        "export": {
                  enabled: true,
                  fileName: "ReorderStock",
              },
        columnChooser: {
            enabled: true
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        groupPanel: {
            visible: true
        },
        columns: [
          {
              dataField: "id",
              caption: "Product ID",
              visible:false,
            },{
              dataField: "product_plu",
              caption: "PLU",
              visible:false,
            },{
              dataField: "productcategory.prodcat_desc",
              caption: "Kategori",
              visible:true,
            },{
              dataField: "brand.brand_name",
              caption: "Merek",
              visible:false,
            },{
              dataField: "product_barcode",
              caption: "Barcode",
              visible:false,
              width:120,
            },{
              dataField: "product_name",
              caption: "Nama Produk",
            },{
              dataField: "product_buy_price",
              caption: "Harga Beli",
              dataType:"number",
              format: "fixedPoint",
              visible:false,
            },{
              dataField: "product_price",
              caption: "Harga Jual",
              dataType:"number",
              format: "fixedPoint",
              visible:false,
            },{
              dataField: "stores.0.product_stores.product_buffer_stock",
              caption: "Buffer Stok",
              width:100,
            },{
              dataField: "product_stock",
              caption: "Stok",
              width:75,
            },
            
        ],
    });
});
</script>
@endsection
