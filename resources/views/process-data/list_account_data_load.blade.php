@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form id="form-container" class="first-group">
    <div id="gridContainer"></div>
    <div id="btnSave" align="right"></div>
</form>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
      const accountdataloads = {!! $accountdataloads !!};
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
                return $("<div class='long-title'><h3>List Of Process Account</h3></div>");
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
      $("#gridContainer").dxDataGrid({
          dataSource: accountdataloads,
          keyExpr: "id",
          showBorders: true,
          scrolling: {
            mode: "virtual"
          },
          allowColumnResizing: true,
          searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
          selection: {
            mode: "single"
          },
          paging: {
              pageSize: 10,
          },
          columns: [{
                dataField: "id",
                caption: "ID",
                visible:false,
            },{
                dataField: "location_name",
                caption: "Location",
            },{
                dataField: "acc_style_caption",
                caption: "Account Style",
            },{
                dataField: "acc_data_qty",
                caption: "Quantity",
                dataType:"number",
                format: "fixedPoint",
            },{
                dataField: "acc_data_tot_cost",
                caption: "Total",
                dataType:"number",
                format: "fixedPoint",
              },
          ],
      });
  });
</script>
@endsection
