@extends('layouts.master')
@section('content')
<div class="loadpanel"></div>
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
                return $("<div class='long-title'><h3>Location List</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "fas fa-sync",
                    hint: 'Syncronize Location and Account', 
                    onClick: function(e) {     
                        loadPanel.show(); 
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
            url: "{{route('location.create')}}"
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
                dataField: "organization.org_name",
                caption: "Organization",
            },{
                dataField: "location_id",
                caption: "Code",
            },{
                dataField: "location_name",
                caption: "Location",
            },{
                dataField: "location_reff_no",
                caption: "Url",
                visible:false,
            },{
                dataField: "location_reff",
                caption: "Report Name",
                visible:false,
            },
        ],
    });
    const loadPanel = $('.loadpanel').dxLoadPanel({
    shadingColor: 'rgba(0,0,0,0.4)',
    position: { of: '#employee' },
    visible: false,
    showIndicator: true,
    showPane: true,
    shading: true,
    hideOnOutsideClick: false,
    onShown() {
            syncronizeLocationAccount(loadPanel,compid);   
        },
        onHidden() {
            // showEmployeeInfo(employee);
        },
    }).dxLoadPanel('instance');

    
});

function syncronizeLocationAccount(loadPanel,compid) {
        $.ajax({
            url: "{{route('location.store')}}",
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

