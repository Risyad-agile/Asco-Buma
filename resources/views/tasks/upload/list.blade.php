@extends('layouts.master')
@section('content')
    <div class="long-title"><h3>Upload Task List</h3></div>
    <div id="gridContainer"></div> 
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
    const uploadtasks = {!! $uploadtasks !!};
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
      
    $("#gridContainer").dxDataGrid({
          dataSource: uploadtasks,
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
                  dataField: "up_task_name",
                  caption: "Task Name",
              },{
                  dataField: "up_task_maker_time",
                  caption: "Created Time",
                  dataType: "datetime",
                },{
                  dataField: "up_task_checker_time",
                  caption: "Checked Time",
                  dataType: "datetime",
              },{
                  dataField: "up_task_approval_time",
                  caption: "Approved Time",
                  dataType: "datetime",
                },{
                  dataField: "up_task_last_message", 
                  caption: "Message", 
                },{
                  dataField: "up_task_progress", 
                  caption: "Progress", 
                },{
                  dataField: "up_task_state",
                  caption: "Status",
                  dataType: "datetime",
              },
          ],
      });
  });
</script>
@endsection
