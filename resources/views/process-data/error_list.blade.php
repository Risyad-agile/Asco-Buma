@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('processdatalog.main')}}">
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
                return $("<div class='long-title'><h3>List Error Of Process Data Load</h3></div>");
            }
             },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "fas fa-envelope",
                    hint: 'Send Email',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        // var txtid=document.getElementById("txtid").value;
                        // if(txtid==""){
                        //     DevExpress.ui.notify({
                        //         message: "Choose the process...",
                        //         position: {
                        //             my: "center top",
                        //             at: "center top"
                        //         }
                        //     }, "warning", 3000);
                        //     e.preventDefault();
                        //     return false;
                        // }
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
                  dataField: "process_log_note",
                  caption: "Note",
              },{
                  dataField: "process_task_status",
                  caption: "Task Status",
              },{
                  dataField: "process_receive_date",
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
