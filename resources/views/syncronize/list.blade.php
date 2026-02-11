@extends('layouts.master')
@section('content')
<div class="loadpanelorg"></div>
<div class="loadpanellocac"></div>
<div id="toolbar"></div>
<div id="gridContainer"></div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
var compid="{!!$company->id!!}";
$("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
            template: function() {
                return $("<div class='long-title'><h3>Syncronize Data With Envizi</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "far fa-building",
                    hint: 'Syncronize Organization', 
                    onClick: function(e) {      
                        loadPanelOrg.show();  
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "	fas fa-landmark",
                    hint: 'Syncronize Location and Account', 
                    onClick: function(e) {    
                        loadPanelLocAcs.show();   
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Close Syncronization',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            }]
    });
    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
            url: "{{route('syncronize.create')}}"
          })
      },    
    });
   
    
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        showBorders: true,
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                label: {
                    text: "ID",
                },
                dataField: "id",
                visible:false, 
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "company.comp_name",
                caption: "Company",
            },{
                dataField: "sync_task_name",
                caption: "Task Name", 
            },{
                dataField: "sync_state_note",
                caption: "Note",
            },{
                dataField: "sync_time",
                caption: "Time",
                dataType: "datetime",
            },
        ],
    });
    const loadPanelOrg = $('.loadpanelorg').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#organization' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        onShown() {
            syncronizeOrganization(loadPanelOrg,compid);       
        },
        onHidden() {
            // showEmployeeInfo(employee);
        },
    }).dxLoadPanel('instance');

    const loadPanelLocAcs = $('.loadpanellocac').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#locationandaccstyle' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        onShown() {
            syncronizeLocationAccount(loadPanelLocAcs,compid);       
        },
        onHidden() {
            // showEmployeeInfo(employee);
        },
    }).dxLoadPanel('instance');
});

function syncronizeOrganization(loadPanel,compid) {
        $.ajax({
            url: "{{route('syncronize.process.organization')}}",
            method: "POST",
            data: {compid:compid},
            dataType: "json",
            success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                loadPanel.hide(); 
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
                loadPanel.hide(); 
                return false;
            }
        });
    }
    function syncronizeLocationAccount(loadPanel,compid) {
        $.ajax({
            url: "{{route('syncronize.process.location.account')}}",
            method: "POST",
            data: {compid:compid},
            dataType: "json",
            success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                loadPanel.hide(); 
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
                loadPanel.hide(); 
                return false;
            }
        });
    }
</script>
@endsection

