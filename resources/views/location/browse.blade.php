@extends('layouts.master')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
body {
    background-color: #f4f6f9;
    font-family: 'Poppins', sans-serif !important;
}

.dashboard-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    padding: 20px;
    border: none;
}

.dx-datagrid {
    font-family: 'Poppins', sans-serif !important;
    border: none !important;
}

.dx-datagrid-headers .dx-row > td {
    background-color: #fff !important;
    color: #8898aa;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    border-bottom: 2px solid #eaecf4 !important;
    padding-top: 15px;
    padding-bottom: 15px;
}

.dx-datagrid-rowsview .dx-row > td {
    padding: 14px;
    font-size: 0.85rem;
}
</style>

<div class="container-fluid px-4">
    <div class="dashboard-card">
        <div id="toolbarContainer"></div>
        <div id="toolbarFilter" class="mb-3"></div>
        <div id="gridContainer"></div>
        <div class="loadpanel"></div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    let selectedOrg = null;
    let browseBox, orgBox;

    const userRole = "{{ $user->role }}"; //"{{ $user->hasRole('superadmin') ? 'superadmin' : 'user' }}";
    const defaultOrgId = "{{ $company->id ?? '' }}";

    // ====================== TOOLBAR ======================
    $("#toolbarContainer").dxToolbar({
        items: [
            {
                location: "before",
                template: () => $("<div>").html('<i class="fas fa-map-marker-alt me-2"></i> Location Management')
                    .css({ fontSize: "18px", fontWeight: "600", color: "#344767" })
            },
            {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "refresh",
                    hint: "Refresh",
                    stylingMode: "outlined",
                    onClick: () => $("#gridContainer").dxDataGrid("instance").refresh()
                }
            },
            {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "fas fa-retweet",
                    hint: "Synchronize Location and Account",
                    stylingMode: "outlined",
                    disabled: true,   // ⬅️ default DISABLED
                    onInitialized: function(e){
                        syncButton = e.component;   // simpan instance button
                    },
                    onClick: function () {
                        loadPanel.show();
                    }
                }
            },
            {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "close",
                    text: "Close",
                    stylingMode: "outlined",
                    onClick: () => window.history.back()
                }
            }
        ]
    });

    // ====================== FILTER BAR ======================
    $("#toolbarFilter").dxToolbar({
        items:[
            {
                location:"before", 
                widget:"dxSelectBox", 
                options:{
                    elementAttr:{id:"orgBox"},
                    displayExpr:"org_name",
                    valueExpr:"id",
                    placeholder:"Filter by organization...",
                    width:300,
                    stylingMode:"outlined",
                    onValueChanged: function(e){
                        filterGrid();

                        // enable / disable tombol sync
                        if(syncButton){
                            syncButton.option("disabled", !e.value);
                        }
                    },
                    dataSource: new DevExpress.data.CustomStore({
                        key: "id",
                        load: function() {
                            return $.getJSON("{{ route('location.combo_org') }}");
                        }
                    }),
                    onInitialized: function(e){
                        const store = e.component.getDataSource();
                        store.load().done(function(items){
                            if(userRole === 'superadmin' && items.length>0){
                                e.component.option("value", items[0].id);
                            } else if(defaultOrgId){
                                e.component.option("value", defaultOrgId);
                            }
                        });
                    }
                }
            },
            { 
                location:"before", 
                widget:"dxTextBox", 
                options:{
                    elementAttr:{id:"browseBox"},
                    placeholder:"Browse location...",
                    mode:"search",
                    width:980,
                    stylingMode:"outlined",
                    onValueChanged: filterGrid
                }
            }
        ]
    });

    orgBox = $("#orgBox").dxSelectBox("instance");
    browseBox = $("#browseBox").dxTextBox("instance");

    // ====================== DATAGRID ======================
    $("#gridContainer").dxDataGrid({
        dataSource: new DevExpress.data.CustomStore({
            key: "id",
            load: function () {
                const params = {
                    search: browseBox.option("value") || '',
                    org_id: orgBox.option("value") || ''
                };

                return $.getJSON("{{ route('location.list') }}", params)
                    .then(result => ({
                        data: result.data || result,
                        totalCount: result.totalCount || result.length
                    }));
            }
        }),

        showBorders: false,
        showRowLines: true,
        showColumnLines: false,
        hoverStateEnabled: true,

        paging: { pageSize: 12 },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 12, 15],
            showInfo: true
        },

        columns: [
            { dataField: "id", visible: false }, 
            { dataField: "org_name", caption: "Organization", minWidth: 150 },
            { dataField: "group_type", caption: "group type", minWidth: 150 },
            { dataField: "group_name1", caption: "group name 1", minWidth: 200 },
            { dataField: "group_name2", caption: "group name 3", minWidth: 250 },
            { dataField: "location_name", caption: "Location Name", minWidth: 200 }
        ]
    });

    function filterGrid() {
        $("#gridContainer").dxDataGrid("instance").getDataSource().reload();
    }

    // ====================== LOAD PANEL (SYNC) ======================
    const loadPanel = $('.loadpanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        onShown() {
           const orgId = orgBox.option("value");

            if (!orgId) {
                loadPanel.hide();
                swal("Warning", "Please select organization first", "warning");
                return;
            }

            syncronizeLocationAccount(loadPanel, orgId);
        }
    }).dxLoadPanel('instance');
});

// ====================== SYNC FUNCTION ======================
function syncronizeLocationAccount(loadPanel, orgId) {
    $.ajax({
        url: "{{route('location.store')}}",
        method: "POST",
        data: { orgid: orgId, _token: "{{ csrf_token() }}" },
        dataType: "json",
        success: function (data) {
            swal({
                title: data.status,
                icon: data.status,
                text: data.message,
                closeModal: true,
            });
            $("#gridContainer").dxDataGrid("instance").refresh();
            loadPanel.hide();
        },
        error: function(jqXHR) {
            swal({
                title: "Error",
                icon: "error",
                text: jqXHR.responseText,
                closeModal: true,
            });
            loadPanel.hide();
        }
    });
}
</script>
@endsection
