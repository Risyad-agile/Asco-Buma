@extends('layouts.master')
@section('content')
    <div class="content"> 
        <div id="toolbar"></div>
        <div id="gridContainer"></div> 
    </div>
@endsection

@section('script')
<script type="text/javascript">
const taskid="{!!$taskid!!}";
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        } 
    });
    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
            url: "{{URL::to('partner-user/task/maker/retrieve/load')}}"+"/"+taskid,
          })
      },
      update: function (key, values) {
          var accstyleid= key.id;
          return $.ajax({
              url: "{{URL::to('partner-user/task/maker/retrieve/update/')}}"+"/"+accstyleid,
              method: "PUT",
              data: values,
              dataType: "json",
              success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: "Error Update Account Style",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: "Success Mapping Account Style",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                swal({
                    title: "Error",
                    icon: "error",
                    text: qXHR.responseText,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                return false;
                }
            });
          return false;
      },
  });
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Update Missing Account Style</h3></div>");
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
                    window.location = "{{route('partner.task.maker.retrieve.list.task')}}";
                }
            }
        }]
    });
    const accstyles={!!$accstyles!!};  
    $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        showBorders: true,
        keyExpr: 'id',
        editing: {
            mode: "batch",
            allowUpdating: true,
            useIcons:true, 
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10 
        },
        columns: [
            {
                dataField: "acc_style_caption_import",
                caption: "Missing Account Style",
            },{
                dataField: "acc_style_caption",
                caption: "Maping Account Style",
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: accstyles,
                    valueExpr: "acc_style_caption",
                    displayExpr: "acc_style_caption",
                }                
            },
        ],
        onEditingStart: function(e){
        if (e.column.dataField != "acc_style_caption") {
             e.cancel = true;
          }
        },
        
    }).dxDataGrid('instance');

    $('#startEditAction').dxSelectBox({
    value: 'click',
    items: ['click', 'dblClick'],
    onValueChanged(data) {
      dataGrid.option('editing.startEditAction', data.value);
    },
  });
});
 
</script>
@endsection
