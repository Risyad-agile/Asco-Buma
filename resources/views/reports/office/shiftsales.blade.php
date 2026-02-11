@extends('layouts.master')
@section('content')
<div id="toolbar"></div> 
<form id="form-container" class="first-group">
      <div class="card">
        <div class="card-body">
          <div id="form"></div>
        </div>
      </div>  
      <div id="gridContainer"></div>
      <div id="action-close"></div>
</form>
@endsection

@section('script')
  <script type="text/javascript">
  $(function() {
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
            return $("<div class='long-title'><h3>Daftar Penjualan Berdasarkan Shift</h3></div>");
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
    var shifting={!!$shift!!};
    $("#form").dxForm({
      colCount: 1,
      formData:shifting,
      readOnly:true,
      items:[
      {
        itemType:"group",
        colCount:2,
        items: [{
          dataField: "shift_no",
          label:{
            text:"Kode Shift",
          },
        },{
          dataField: "shift_date",
          label:{
            text:"Tanggal Awal",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              // value : new Date(),
          }
        },{
          dataField: "stores.store_name",
          label:{
            text:"Toko ",
          },
          editorOptions: {
          }
        },{
          dataField: "shift_date",
          label:{
            text:"Tanggal Akhir ",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              // value : new Date(),
          }
        },{
            dataField: "shift_sale_totals",
            label:{
              text:"Total Sales Shift",
            },  
            editorOptions: {
                dataType: "number",
                format: "fixedPoint",
             },
            width: 100,
        },]
      },]
    }); 
    $('#action-close').dxSpeedDialAction({
      label: 'Close',
      icon: 'clear',
      index: 1,
      onClick() { 
        window.location = '{{route('home')}}';

      },
    }).dxSpeedDialAction('instance');
    var sales={!!$sales!!};
    var dataGrid = $("#gridContainer").dxDataGrid({
          dataSource: sales,
          keyExpr: "sale_no",
          showBorders: true,
          scrolling: {
            mode: "virtual"
          },
          // "export": {
          //       enabled: true,
          //       fileName: "Shift_Detail",
          //       allowExportSelectedData: true
          //   },
          allowColumnResizing: true,
          columns: [{
                  dataField: "sale_no",
                  caption: "No Sales",
              },{
                  dataField: "sale_date",
                  caption: "Tanggal",
                  dataType: "date",
                  format:'dd-MM-yyyy',
              },{
                  dataField: "sale_service_charge",
                  caption: "Srv Charge",
                  dataType: "number",
                  format: "fixedPoint",
              },{
                  dataField: "sale_tax",
                  caption: "Pajak",
                  dataType: "number",
                  format: "fixedPoint",
              },{
                  dataField: "sale_total",
                  caption: "Total Sales",
                  dataType: "number",
                  format: "fixedPoint",
              },
          ],
          summary: {
              totalItems: [{
                  column: "sale_no",
                  summaryType: "count",
                  displayFormat: "Items {0}",
              },{
                  column: "sale_total",
                  summaryType: "sum",
                  dataType: "number",
                  valueFormat: "fixedPoint",
                  displayFormat: "Total Transaksi {0}",
                  // showInGroupFooter: true
          }]},
          masterDetail: {
            enabled: true,
            template: function(container, options) {
                var roProductData = options.data;
                $("<div>")
                    .dxDataGrid({
                        columnAutoWidth: true,
                        showBorders: true,
                        columns: [
                        {
                            dataField: "product_id",
                            caption: "Produk Id",
                        },{
                            dataField: "product_desc",
                            caption: "Deskripsi Produk",
                        },{
                            dataField: "saleproducts.sale_product_price",
                            caption: "Harga",
                            dataType: "number",
                            format: "fixedPoint",
                        },{
                            dataField: "saleproducts.sale_product_qty",
                            caption: "Jumlah",
                        },{
                            caption: "Total",
                            dataType: "number",
                            format: "fixedPoint",
                            calculateCellValue: function(rowData) {
                                var harga=rowData.saleproducts.sale_product_price;
                                var jumlah=rowData.saleproducts.sale_product_qty;
                                return harga*jumlah;
                                // return rowData.Status == "Completed";
                            }
                        }],
                        summary: {
                            totalItems: [{
                                column: "product_id",
                                summaryType: "count",
                                displayFormat: "Jumlah Items {0}",
                            },{
                                column: "Total",
                                summaryType: "sum",
                                dataType: "number",
                                valueFormat: "fixedPoint",
                                displayFormat: "Total Transaksi {0}",
                                // showInGroupFooter: true
                        }]},
                        dataSource: roProductData.products
                    }).appendTo(container);
              }
          }
      });
  });
  </script>
@endsection
