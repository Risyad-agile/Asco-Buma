@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('partner.task.checker.main')}}">
    @csrf 
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="taskid"
      class="form-control" placeholder="Upload Task ID" hidden>
      <input id="txtstate" type="text" name="state"
      class="form-control" placeholder="Upload Task ID" hidden>
</form>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
      const tasks = {!! $tasks !!};
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
                return $("<div class='long-title'><h3>List Task Waiting For Check</h3></div>");
            }
             },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "folder",
                    hint: 'Open Detail and Check',
                    useSubmitBehavior: true,
                    onClick: function(e) {     
                      const uptask_id=document.getElementById("txtid").value;
                      const state=document.getElementById("txtstate").value;
                      if(uptask_id==""){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please Choose Task to Check...",
                              footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                          })
                          e.preventDefault();
                          return false;                       
                      }
                      if(state!="Created"){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "These Task Already Process...",
                              footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                          })
                          e.preventDefault();
                          return false;                       
                      }
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "trash",
                    hint: 'Delete Task and Detail',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Close',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            }]
        });
      $("#gridContainer").dxDataGrid({
          dataSource: tasks,
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
                  dataField: "task_name",
                  caption: "Task Name",
                },{
                  dataField: "task_checker_name",
                  caption: "Checker Name",
                 },{
                  dataField: "task_checker_time",
                  caption: "Checker Time",
                  dataType: "datetime",
                },{
                  dataField: "task_last_message",
                  caption: "Message", 
                },{
                  dataField: "task_state",
                  caption: "Status", 
              },
          ],
          onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            if(data) { 
                $("#txtid").val(data.id); 
                $("#txtstate").val(data.task_state); 
            }
        },
      });
  });
</script>
@endsection
