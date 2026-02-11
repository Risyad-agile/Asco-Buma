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
    let searchBox;

    // ======================
    // TOOLBAR ATAS
    // ======================
    $("#toolbarContainer").dxToolbar({
        items: [
            {
                location: "before",
                template: () => $("<div>")
                    .html('<i class="fas fa-building me-2"></i> Company Management')
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
                    onClick: () => window.location.href = "{{ route('companies.create') }}"
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
                    elementAttr: { id: "btnEditCompany" },
                    onClick: function () {
                        if (!selectedRow) return;
                        window.location.href = "{{ url('asri-core/companies/update') }}/" + selectedRow.id ;
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
            }
        ]
    });

    // ======================
    // FILTER BAR (SEARCH)
    // ======================
    $("#toolbarFilter").dxToolbar({
        items: [
            {
                location: "before",
                widget: "dxTextBox",
                options: {
                    elementAttr: { id: "searchBox" },
                    placeholder: "Search company...",
                    mode: "search",
                    width: 1308,
                    stylingMode: "outlined",
                    onValueChanged: function () {
                        filterGrid();
                    }
                }
            }
        ]
    });

    searchBox = $("#searchBox").dxTextBox("instance");

    // ======================
    // DATAGRID
    // ======================
    $("#gridContainer").dxDataGrid({
        dataSource: new DevExpress.data.CustomStore({
            key: "id",
            load: function () {
                const params = {
                    search: searchBox.option("value") || ''
                };

                return $.getJSON("{{ route('companies.list') }}", params)
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
            { dataField: "comp_name", caption: "Company Name", minWidth: 200 },
            { dataField: "comp_address", caption: "Address", minWidth: 220 },
            { dataField: "comp_city", caption: "City", width: 120 },
            { dataField: "comp_phone", caption: "Phone", width: 130 },
            { dataField: "comp_email", caption: "Email", width: 180 }
        ],

        onSelectionChanged: function(e) {
            selectedRow = e.selectedRowsData[0];
            $("#btnEditCompany").dxButton("instance")
                .option("disabled", !selectedRow);
        }
    });

    function filterGrid() {
        $("#gridContainer").dxDataGrid("instance").getDataSource().reload();
    }
});
</script>
@endsection
