@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Stok Produk {!! $storename !!}</h3></div>
<form id="form-container" class="first-group">
    <div class="second-group">
      <div id="gridProduk"></div>
    </div>
</form>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $("#gridProduk").dxDataGrid({
        dataSource: {!! $products !!},
        keyExpr: "product_id",
        showBorders: true,
        "export": {
                  enabled: true,
                  fileName: "ProdList",
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
              dataField: "product_id",
              caption: "Product ID",
              visible:false,
              // width:100,
            },{
              dataField: "product_plu",
              caption: "PLU",
              visible:false,
              // width:100,
            },{
              dataField: "prodcat_id",
              caption: "ID Kategori",
              visible:false,
            },{
              dataField: "prodcat_desc",
              caption: "Kategori",
              visible:false,
            },{
              dataField: "brand_id",
              caption: "ID Merek",
              visible:false,  
            },{
              dataField: "brand_desc",
              caption: "Merek",
              visible:false,
            },{
              dataField: "product_barcode",
              caption: "Barcode",
              visible:true,
              // width:120,
            },{
              dataField: "product_name",
              caption: "Produk",
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
              dataField: "product_stock",
              caption: "Stok",
              // width:75,
            },
            
        ],
        onRowInserting: function(e){

        },
        onEditingStart: function(e){
        },
    });
});
</script>
@endsection
