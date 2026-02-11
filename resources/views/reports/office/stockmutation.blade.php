@extends('layouts.master')
@section('content')
    <div class="long-title"><h3>Daftar Mutasi Stok Seluruh Toko</h3></div>
    <div id="gridProduk"></div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $("#gridProduk").dxDataGrid({
        dataSource: {!! $reportStockMutationList!!},
        // keyExpr: "product_id",
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
              dataField: "rsm_product_id",
              caption: "Product ID",
              width:150,
            },{
              dataField: "rsm_product_desc",
              caption: "Nama", 
            // },{
            //   dataField: "rsm_product_category_id",
            //   caption: "Kategori",
            //   groupIndex: 0,
            //   visible:false,
            },{
              dataField: "rsm_product_category",
              caption: "Kategori",
              groupIndex: 0,
            },{
              dataField: "startQty",
              caption: "Qty Awal",
              calculateCellValue: function(rowData) {
                      var startin=rowData.rsm_product_start_in_qty;
                      var startout=rowData.rsm_product_start_out_qty;
                      var startqty=startin-startout;
                    //   if(rowData.sale_product_total!=0){
                    //     total=rowData.sale_product_total;
                    //   }
                      return startqty;
                    },
            //   visible:true,
               width:90,

            // },{
            //   dataField: "rsm_product_start_out_price",
            //   caption: "Harga",
            //   dataType:"number",
            //   format: "fixedPoint",
            },{
              dataField: "rsm_product_in_qty",
              caption: "Qty Masuk",
              width:90,

            //   visible:false,
            },{
              dataField: "rsm_product_out_qty",
              caption: "Qty Keluar",
              width:90,

            //   visible:false,
            //   width:120,
            // },{
            //   dataField: "product_desc",
            //   caption: "Deskripsi",
            },{
              dataField: "product_buy_price",
              caption: "Harga Beli",
              dataType:"number",
              format: "fixedPoint",
              visible:false,
            },{
              dataField: "rsm_product_final_qty",
              caption: "Harga Jual",
              dataType:"number",
              format: "fixedPoint",
              visible:false,
            },{
              dataField: "final",
              caption: "Final",
              width:75,
              calculateCellValue: function(rowData) {
                var startin=rowData.rsm_product_start_in_qty;
                var startout=rowData.rsm_product_start_out_qty;
                var stockin=rowData.rsm_product_in_qty;
                var stockout=rowData.rsm_product_out_qty;
                var final=(startin-startout)+stockin-stockout;
            //         //   if(rowData.sale_product_total!=0){
            //         //     total=rowData.sale_product_total;
            //         //   }
                return final;
                }
            },
            
        ],
    });
});
</script>
@endsection
