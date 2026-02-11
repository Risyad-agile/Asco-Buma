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
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    let selectedRow = null;
    let browseBox, companyBox;

    // ======================
    // TOOLBAR ATAS
    // ======================
    $("#toolbarContainer").dxToolbar({
        items: [
            {
                location: "before",
                template: () => $("<div>")
                    .html('<i class="fas fa-sitemap me-2"></i> Organization Management')
                    .css({ fontSize: "18px", fontWeight: "600", color: "#344767" })
            },
            {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "plus",
                    text: "Add",
                    type: "success",
                    stylingMode: "outlined",
                    onClick: () => window.location.href = "{{ route('organizations.create') }}"
                }
            },
            {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "edit",
                    text: "Edit",
                    type: "default",
                    stylingMode: "outlined",
                    disabled: true,
                    elementAttr: { id: "btnEditOrg" },
                    onClick: function () {
                        if (!selectedRow) return;
                        window.location.href = "{{ route('organizations.edit', ':id') }}".replace(':id', selectedRow.id);
                    }
                }
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
                    icon: "close",
                    text: "Close",
                    stylingMode: "outlined",
                    onClick: () => window.history.back()
                }
            }
        ]
    });

    // ======================
    // FILTER BAR (COMPANY + BROWSE)
    // ======================
    $("#toolbarFilter").dxToolbar({
        items: [
            {
                location: "before",
                widget: "dxSelectBox",
                options: {
                    elementAttr: { id: "companyBox" },
                    dataSource: new DevExpress.data.CustomStore({
                        load: function () {
                            return $.getJSON("{{ route('companies.combo') }}");
                        }
                    }),
                    displayExpr: "comp_name",
                    valueExpr: "id",
                    placeholder: "Filter by company...",
                    width: 300,
                    stylingMode: "outlined",
                    onValueChanged: function () {
                        filterGrid();
                    }
                }
            },
            {
                location: "before",
                widget: "dxTextBox",
                options: {
                    elementAttr: { id: "browseBox" },
                    placeholder: "Browse organization...",
                    mode: "search",
                    width: 980,
                    stylingMode: "outlined",
                    onValueChanged: function () {
                        filterGrid();
                    }
                }
            }
        ]
    });

    companyBox = $("#companyBox").dxSelectBox("instance");
    browseBox = $("#browseBox").dxTextBox("instance");

    // ======================
    // DATAGRID
    // ======================
    $("#gridContainer").dxDataGrid({
        dataSource: new DevExpress.data.CustomStore({
            key: "id",
            load: function () {
                const params = {
                    search: browseBox.option("value") || '',
                    comp_id: companyBox.option("value") || ''
                };

                return $.getJSON("{{ route('organizations.list') }}", params)
                    .then(result => {
                        return {
                            data: result.data || result,
                            totalCount: result.totalCount || result.length
                        };
                    });
            }
        }),

        showBorders: false,
        showRowLines: true,
        showColumnLines: false,
        hoverStateEnabled: true,

        selection: { mode: "single" },

        paging: { pageSize: 12 },
        pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [10, 12, 15],
            showInfo: true
        },

        columns: [
            { dataField: "id", visible: false }, 
            { dataField: "org_name", caption: "Organization Name", minWidth: 220 },
            { dataField: "company.comp_name", caption: "Company", minWidth: 200 },
            { dataField: "org_link", caption: "Link", minWidth: 200 },
            { dataField: "org_state", caption: "Status", width: 100,
                cellTemplate: function(container, options) {
                    let badge = options.value == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';
                    container.html(badge);
                }
            }
        ],

        onSelectionChanged: function(e) {
            selectedRow = e.selectedRowsData[0];
            $("#btnEditOrg").dxButton("instance")
                .option("disabled", !selectedRow);
        }
    });

    function filterGrid() {
        $("#gridContainer").dxDataGrid("instance").getDataSource().reload();
    }
});
</script>
@endsection
