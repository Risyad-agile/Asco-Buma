@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div class="short-title"><h4>{!!$stores->store_name!!}</h4></div>
<form id="form-container" class="first-group">
      <div id="gridProduct"></div>
      <input id="hidtglrep" type="hidden" value="{!!$bulanlap!!}">
      <input id="hidstoreid" type="hidden" value="{!!$stores->store_id!!}">
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
            return $("<div class='long-title'><h3>Laporan Penjualan & Pembayaran {{$bulanlap}}</h3></div>");
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

    var sale = {!! $sales !!};
    var tgl =document.getElementById("hidtglrep").value;
    var storeid=document.getElementById("hidstoreid").value;

    var dataGrid = $("#gridProduct").dxDataGrid({
            dataSource: sale,
            allowColumnReordering: true,
            showBorders: true,
            grouping: {
                autoExpandAll: true,
            },
            searchPanel: {
                visible: true
            },
            paging: {
                pageSize: 10,
            },
            pager: {
                showPageSizeSelector: true,
                allowedPageSizes: [5, 10, 15],
                showInfo: true
            },
            // groupPanel: {
            //     visible: true
            // },
            "export": {
                enabled: true,
                fileName: "Sales",
                allowExportSelectedData: true
            },
            columns: [
                {
                  dataField:"sale_no",
                  caption: "No Sales",
                },{
                  dataField:"sale_date",
                  caption: "Tanggal",  
                  dataType: "date",
                  format:'dd-MM-yyyy',
                },{
                  dataField:"sale_prod",
                  caption: "Produk",
                  format: "fixedPoint",
                },{
                  dataField:"sale_food",
                  caption: "Food",
                  format: "fixedPoint",
                },{
                  dataField:"sale_beverage",
                  caption: "Bev",
                  format: "fixedPoint",
                },{
                  dataField:"sale_total",
                  caption: "Jumlah",
                  format: "fixedPoint",
                },{
                  dataField:"sale_disc",
                  caption: "Disc",
                  format: "fixedPoint",
                },{
                  dataField:"sale_service_charge",
                  caption: "Service",
                  format: "fixedPoint",
                },{
                  dataField:"sale_tax",
                  caption: "Tax",
                  format: "fixedPoint",
 
                },{
                  caption: "Gross",
                  dataType: "number",
                  format: "fixedPoint",
                  calculateCellValue: function(rowData) {
                      var jumlah=rowData.sale_total;
                      var servis=rowData.sale_service_charge;
                      var gprofit=jumlah-servis;
                      return gprofit;
                    }
                },{
                  dataField:"totalsales",
                  caption: "Total",
                  format: "fixedPoint",
                },
            ],
            sortByGroupSummaryInfo: [{
                summaryItem: "count"
            }],
            summary: {
                groupItems: [{
                  column: "Jumlah",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Qty : {0}",
                    showInGroupFooter: true,
                },{
                    column: "Total",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Total : {0}",
                    showInGroupFooter: true,
                },{
                    column: "Gross Profit",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: " {0}",
                    showInGroupFooter: true,    
                }],
              totalItems: [{
                    column: "product_id",
                    summaryType: "count",
                    displayFormat: "Items {0}",
                },{
                    column: "Total",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Tot. Sales {0}",
                },{
                    column: "Gross Profit",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Gr. Profit {0}",    
                },{
                    column: "Tax",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Tax {0}",    
                 },{
                    column: "Service",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Srv {0}",    
                }]
            }
        }).dxDataGrid("instance");
  });
  </script>
@endsection
