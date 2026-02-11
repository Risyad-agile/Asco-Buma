@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('partner.processdatalog.main')}}">
    @csrf 
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="processid"
      class="form-control" placeholder="Process ID" hidden>
</form>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
      const processdata = {!! $processdata !!};
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
                return $("<div class='long-title'><h3>List Of Processing Receive Data</h3></div>");
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
          dataSource: processdata,
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
                  dataField: "process_data_note",
                  caption: "Note",
                },{
                  dataField: "process_data_describe_state",
                  caption: "State",
              },{
                  dataField: "process_data_process_time",
                  caption: "Receive Time",
                  dataType: "datetime",
              },
          ],
          onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            if(data) { 
                $("#txtid").val(data.id); 
            }
        },
      });
  });
</script>
@endsection
