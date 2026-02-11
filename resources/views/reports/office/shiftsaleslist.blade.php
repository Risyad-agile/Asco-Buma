@extends('layouts.master')
@section('content')
<div id="toolbar"></div> 
<div class="short-title"><h4>{!!$store->store_name!!}</h4></div>
<form method="POST" action="{{route('office.reports.sales.shift.main')}}">
  @csrf 
      <div id="gridProduct"></div>
      <div id="action-add"></div>
      <input id="shiftno" type="text" name="shiftno"
              class="form-control" placeholder="Shift No" hidden>
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
            return $("<div class='long-title'><h3>Daftar Shift Bulan ini</h3></div>");
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
    $('#action-add').dxSpeedDialAction({
      label: 'Close',
      icon: 'clear',
      index: 1,
      onClick() { 
        window.location = '{{route('home')}}';

      },
    }).dxSpeedDialAction('instance');

    var shifts={!!$shifts!!};
    var dataGrid = $("#gridProduct").dxDataGrid({
            dataSource: shifts,
            allowColumnReordering: true,
            hoverStateEnabled: true,
            selection: {
                mode: "single"
            },
            showBorders: true,
            searchPanel: {
                visible: true
            },
            // scrolling: {
            //   mode: "virtual"
            // },
            "export": {
                enabled: true,
                fileName: "Shift",
                allowExportSelectedData: true
            },
            columns: [
                {
                  dataField:"shift_no",
                  caption: "Kode Shift",
                },{
                  dataField:"shift_date",
                  caption: "Tanggal",
                  dataType: "date",
                  format: "dd-MM-yyyy",
                },{
                  dataField:"shift_opening",
                  caption: "Opening",
                  dataType: "number",
                  format: "fixedPoint",
                },{
                  dataField:"shift_closing",
                  caption: "Closing",
                  dataType: "number",
                  format: "fixedPoint",
                },{
                  dataField:"shift_sale_totals",
                  caption: "Total Transaksi",
                  dataType: "number",
                  format: "fixedPoint",
                },
            ],
            sortByGroupSummaryInfo: [{
                summaryItem: "count"
            }],
            onContentReady: function(e) {
                var $customButton = $('<div>').dxButton({
                    icon: 'doc', //or your custom icon
                    hint: 'Lihat Detail Laporan Penjualan',
                    useSubmitBehavior: true,
                    onClick: function() {
                      var shiftno=document.getElementById("shiftno").value;
                      if(shiftno==""){
                          DevExpress.ui.notify({
                              message: "Silakan pilih Shift untuk melihat detail Laporan Penjualan...",
                              position: {
                                  my: "center top",
                                  at: "center top"
                              }
                          }, "warning", 3000);
                          e.preventDefault();
                          // event.preventDefault();
                          return false;
                      }
                    }
                });
                
                e.element
                    .find('.dx-datagrid-header-panel')
                    .append($customButton);
            },
            onSelectionChanged: function (selectedItems) {
              
              var data = selectedItems.selectedRowsData[0];
              console.log(data.shift_no);
              $("#shiftno").val(data.shift_no); //kirim perintah update ke server



            },
            summary: {
              totalItems: [{
                    column: "shift_no",
                    summaryType: "count",
                    displayFormat: "Shifts {0}",
                },{
                    column: "shift_sale_totals",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Tot. Sales {0}",  
                }]
            }
        }).dxDataGrid("instance");
  });
  </script>
@endsection
