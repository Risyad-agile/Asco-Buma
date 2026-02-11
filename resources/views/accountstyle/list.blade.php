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
    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Account Style List</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "fas fa-sync",
                    hint: 'Syncronize Account', 
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
                url: "{{route('accountstyle.create')}}"
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
            columns: [{
                    dataField: "id",
                    caption: "ID",
                    visible:false,
                },{
                    dataField: "acc_style_link",
                    caption: "Link",
                },{
                    dataField: "acc_style_caption",
                    caption: "Caption",
                },{
                    dataField: "acc_style_scope",
                    caption: "Scope",
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
            syncronizeAccount(loadPanel);       
            },
            onHidden() {
                // showEmployeeInfo(employee);
            },
        }).dxLoadPanel('instance');
});
function syncronizeAccount(loadPanel) {
        $.ajax({
            url: "{{route('accountstyles.sync')}}",
            method: "POST",
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


