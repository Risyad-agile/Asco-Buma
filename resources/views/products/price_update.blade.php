@extends('layouts.master')
@section('content')
    <div id="toolbar"> </div>
    <div id="tabProdPrice"></div>
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
            return $("<div class='long-title'><h3>Pembaharuan Harga Produk</h3></div>");
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


  var stores ={!! $stores !!}
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
        var deferred = $.Deferred();
        $.ajax({
            url: "{{route('products.price.load')}}",
            method: "POST",
            data: {stores},
            dataType: "json",
            success: function (data) {
                deferred.resolve(data)
            },
            
        });
        return deferred.promise();
      },
      update: function (key, values) {
          var productid= key.id;
          return $.ajax({
              url: "{{URL::to('farma/products/price/update')}}"+"/"+productid,
              method: "PUT",
              data: {values,stores,productid},
          })
      }
  });

  $("#tabProdPrice").dxDataGrid({
        dataSource: gridDataSource,
        showBorders: true,
        "export": {
                enabled: true,
                fileName: "ProdPriceList",
            },
        paging: {
            enabled: false
        },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [5, 10, 15],
            showInfo: true
        },
        allowColumnResizing: true,
        columnChooser: {
            enabled: true
        },
        editing: {
            mode: "batch",
            allowUpdating: true,
            useIcons: true,
        },
        paging: {
            pageSize: 10
        },
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true,
        },
        columns: [
            {
              dataField: "id",
              caption: "ID",
              visible:false,
            //   width:150,
            },{
              dataField:"productcategory.prodcat_desc",
              caption: "Kategori",
            //   width:200,
            },{
                dataField:"product_name",
                caption:"Produk",
            },{
                dataField:"product_buy_price",
                caption:"Beli",
                dataType:"number",
                format: "fixedPoint",
                // width:125,
                validationRules: [{
                    min:0,
                    type: "required",
                    message: "Masukan Angka..."
                }]
          },{
              dataField:"product_price",
              caption:"Jual",
              dataType:"number",
              format: "fixedPoint",
            //   width:125,
              editorType: "dxNumberBox",
              editorOptions: { 
                  dataType:"number",
                  format: "#,##0",
              },
              validationRules: [{
                  min:0,
                  type: "required",
                  message: "Masukan Angka..."
              }]
          },{
            caption: "Margin",
            dataType: "number",
            // width:100,
            format: "percent",
            calculateCellValue: function(rowData) {
                var hargabeli=rowData.product_buy_price;
                var hargajual=rowData.product_price;
                var margin=(hargajual-hargabeli)/hargabeli;
                if(hargabeli==0 && hargajual==0){
                    margin=0;
                }
                return margin;
              }
          },
      ],

      onEditingStart: function(e){
          if (e.column.dataField != "product_buy_price" && e.column.dataField != "product_price") {
             e.cancel = true;
          }
      },
      onRowUpdated:function(e){
        DevExpress.ui.notify("Harga Berhasil di Perbaharui"); 
      },
  });
  
});
</script>
@endsection


