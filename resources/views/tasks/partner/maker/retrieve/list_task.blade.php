@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('partner.task.maker.retrieve.data.open')}}">
    @csrf 
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="taskid" class="form-control" placeholder="Upload Task ID" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" placeholder="Upload Task ID" hidden>
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
                return $("<div class='long-title'><h3>Updating Task From Retriever</h3></div>");
            }
            },{
              location: 'after',
              widget: 'dxButton',
              locateInMenu: 'auto',
              options: {
                  icon: "folder",
                  hint: 'Open Retriever Data',
                  useSubmitBehavior: true,
                  onClick: function(e) {     
                    const id=document.getElementById("txtid").value;
                    const state=document.getElementById("txtstate").value;
                    if(id==""){
                        Swal.fire({
                            icon: "error",
                            title: "Validation Error",
                            text: "Please choose task",
                            footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                        })
                        e.preventDefault();
                        return false;                       
                    }
                    if(state!="Prepared"){
                        Swal.fire({
                            icon: "error",
                            title: "Validation Error",
                            text: "Data Already Submited, can't open Detail",
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
                  dataField: "task_maker_name",
                  caption: "Maker Name",
                 },{
                  dataField: "task_maker_time",
                  caption: "Create Time",
                  dataType: "datetime",
                },{
                  dataField: "task_last_message",
                  caption: "Message", 
                },{
                  dataField: "task_state",
                  caption: "State", 
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
