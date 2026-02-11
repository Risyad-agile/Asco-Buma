@extends('layouts.master')
@section('content')
    <div id="toolbar"></div>
    <div id="gridContainer"></div> 
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
                    return $("<div class='long-title'><h3>List Task of {!!$company->comp_name!!}</h3></div>");
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
                  dataField: "task_maker_time",
                  caption: "Created Time",
                  dataType: "datetime", 
              },{
                  dataField: "task_approval_type", 
                  caption: "Approval Type", 
              },{
                  dataField: "task_last_message", 
                  caption: "Message",
                },{
                  dataField: "task_state", 
                  caption: "State",
              },
          ],
      });
  });
</script>
@endsection
