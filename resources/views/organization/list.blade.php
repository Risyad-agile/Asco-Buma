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
const compid="{!!$company->id!!}";
const orgs={!!$orgs!!};
$("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
            template: function() {
                return $("<div class='long-title'><h3>Organization List</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "fas fa-sync",
                    hint: 'Syncronize Organization', 
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
    // var gridDataSource = new DevExpress.data.DataSource({
    //   load: function (key) {
    //       return $.ajax({
    //         url: "{{route('organization.create')}}"
    //       })
    //   },    
    // });
   
    
  $("#gridContainer").dxDataGrid({
        dataSource: orgs,
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
                dataField: "org_link",
                caption: "Code",
            },{
                dataField: "org_name",
                caption: "Organization",
            },{
                dataField: "org_group_type",
                caption: "Url",
                visible:false,
            },{
                dataField: "org_group_hierarchy_name",
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
            syncronizeOrganization(loadPanel,compid);       
            },
            onHidden() {
                // showEmployeeInfo(employee);
            },
        }).dxLoadPanel('instance');
});

function syncronizeOrganization(loadPanel,compid) {
    $.ajax({
        url: "{{route('organization.store')}}",
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
                loadPanel.hide(); 
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
            location.reload();
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

