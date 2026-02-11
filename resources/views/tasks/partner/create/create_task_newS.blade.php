@extends('layouts.master')

@section('content')
    <div id="taskNew" style="margin-top: 10px;"></div>
@endsection

@section('script')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var locationDataSource = new DevExpress.data.CustomStore({
        loadMode: "raw",
        load: function () {
            return $.ajax({
                url: "{{ route('partner.getLocations') }}",
                type: "GET",
                dataType: "json"
            });
        }
    });
    var isUpdating = false;
    var accountStyleClientDataSource = new DevExpress.data.CustomStore({
        loadMode: "raw",
        load: function () {
            return $.ajax({
                url: "{{ route('partner.getAccountStylesClientS') }}",
                type: "GET",
                dataType: "json"
            });
        }
    });

    var gridDataSource = new DevExpress.data.DataSource({
        store: {
            type: "array",
            key: "id",
            insert: function (values) {
                return $.ajax({
                    url: "{{ route('create.task.crud.newS') }}",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ data: values }),
                    success: function (response) {
                        DevExpress.ui.notify("Data successfully added", "success", 2000);
                    },
                    error: function (xhr) {
                        let errorMessage = xhr.responseText || "Unknown error";
                        DevExpress.ui.notify("Insert failed: " + errorMessage, "error", 2000);
                    },
                });
            }
        }
    });

    $("#taskNew").dxDataGrid({
        dataSource: gridDataSource,
        showBorders: true,
        paging: { pageSize: 18 },
        pager: { showPageSizeSelector: false, allowedPageSizes: [5, 10, 15], showInfo: true },
        allowColumnResizing: true,
        editing: {
            mode: "batch",
            allowUpdating: true,
            allowAdding: true,
            allowDeleting: true,
            useIcons: true
        },
        searchPanel: { visible: true, highlightCaseSensitive: true },
        columns: [             
            {   dataField: "location_name", caption: "Location", width: 180, 
                lookup: { dataSource: locationDataSource, 
                          valueExpr: "location_name", 
                          displayExpr: "location_name" } 
            },
            {   dataField: "acc_style_mtra_caption", caption: "Account Style", allowEditing : true, width: 180, 
                lookup: {   dataSource: accountStyleClientDataSource, 
                            valueExpr : "acc_style_mtra_caption",
                            displayExpr : "acc_style_mtra_caption"  },
                editorOptions : {
                        onSelectionChanged: function (e) {
                            if (e.selectedItem && !isUpdating) {
                                isUpdating = true;

                                var selected = e.selectedItem;
                                var grid = $("#taskNew").dxDataGrid("instance");
                                if (!grid) {
                                    grid = $(e.element).closest(".dx-datagrid").dxDataGrid("instance");
                                }

                                if (grid) {
                                    var editingRowKey = grid.option("editing.editRowKey");
                                    var rowIndex = grid.getRowIndexByKey(editingRowKey);

                                    if (rowIndex !== -1 && rowIndex !== undefined) {
                                        grid.cellValue(rowIndex, "acc_style_caption", selected.acc_style_caption);
                                        grid.cellValue(rowIndex, "acc_style_mtra_caption", selected.acc_style_mtra_caption);
                                    }
                                }

                                isUpdating = false;
                            }
                        }
                }
            },
            { dataField: "acc_style_caption", caption: "Account Style Note", width: 180},
            { dataField: "record_date_start", caption: "Date Start", dataType: "date", format: "yyyy-MM-dd", alignment : "center", width: 90 },
            { dataField: "record_date_end", caption: "Date End", dataType: "date", format: "yyyy-MM-dd", alignment : "center", width: 90 }, 
            { dataField: "csr_male", caption: "Male", dataType: "number", format: "fixedPoint",  alignment : "center", width: 55},
            { dataField: "csr_female", caption: "FMale", dataType: "number", format: "fixedPoint", alignment : "center", width: 55 },
            { dataField: "csr_less_30", caption: "<30", dataType: "number", format: "fixedPoint", alignment : "center", width: 55,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("<30") // tetap <30 di tampilan
                        .attr("title", "Less than 30") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_between_30_50", caption: "30–50", dataType: "number", format: "fixedPoint", alignment : "center", width: 60,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("30–50") // tetap 30–50 di tampilan
                        .attr("title", "Between 30 and 50") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_more_50", caption: ">50", dataType: "number", format: "fixedPoint", alignment : "center", width: 55,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text(">50") // tetap >50 di tampilan
                        .attr("title", "More than 50") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_phd", caption: "S3", dataType: "number", format: "fixedPoint", alignment : "center", width: 50 },
            { dataField: "csr_post_graduate", caption: "S2", dataType: "number", format: "fixedPoint", alignment : "center", width: 50 },
            { dataField: "csr_bachelor_degree", caption: "S1", dataType: "number", format: "fixedPoint", alignment : "center", width: 50 },
            { dataField: "csr_high_school", caption: "HS", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("HS") // tetap HS di tampilan
                        .attr("title", "High School") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_junior_high_school", caption: "JHS", dataType: "number", format: "fixedPoint", alignment : "center", width: 55,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("JHS") // tetap JHS di tampilan
                        .attr("title", "Junior High School") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_elementary_school", caption: "ES", dataType: "number", format: "fixedPoint", alignment : "center", width: 40,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("ES") // tetap ES di tampilan
                        .attr("title", "Elementary School") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_eduction_other", caption: "OS", dataType: "number", format: "fixedPoint", alignment : "center", width: 35,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("OS") // tetap OS di tampilan
                        .attr("title", "Other School") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_islam", caption: "Ism", dataType: "number", format: "fixedPoint", alignment : "center", width: 40,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Ism") // tetap Ism di tampilan
                        .attr("title", "Islam") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_budha", caption: "Bdh", dataType: "number", format: "fixedPoint", alignment : "center", width: 40, 
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Bdh") // tetap Bdh di tampilan
                        .attr("title", "Buddhist") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_hindu", caption: "Hdu", dataType: "number", format: "fixedPoint", alignment : "center", width: 40,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Hdu") // tetap Hdu di tampilan
                        .attr("title", "Hindu") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_katolik", caption: "Kat", dataType: "number", format: "fixedPoint", alignment : "center", width: 40,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Cat") // tetap Kat di tampilan
                        .attr("title", "Catholic") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_kristen", caption: "Krs", dataType: "number", format: "fixedPoint", alignment : "center", width: 40,
                headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Krs") // tetap Krs di tampilan
                        .attr("title", "Christian") // ini yang jadi hint
                        .appendTo(header);
                }
             },
            { dataField: "csr_religion_other", caption: "ORg", dataType: "number", format: "fixedPoint", alignment : "center", width: 40 },
        ],
        toolbar: {
            items: [
                {
                    location: "before",
                    template: function () {
                        return $("<div>")
                            .text("Task: Data Capture CSR")
                            .css({ "font-size": "25px", "font-weight": "bold", "margin-right": "20px" });
                    }
                },
                "addRowButton",
                {
                    location: "after",
                    widget: "dxButton",
                    options: {
                        icon: "exportxlsx",
                        hint: "Export to Excel",
                        onClick: function () {
                            $("#taskNew").dxDataGrid("instance").exportToExcel(false);
                        }
                    }
                },
                {
                    location: "after",
                    widget: "dxButton",
                    options: {
                        icon: "save",
                        hint: "Save Task",
                        onClick: function () {
                            var grid = $("#taskNew").dxDataGrid("instance");
                            var changes = grid.option("editing.changes") || [];
                            var newData = changes.filter(c => c.type !== "remove").map(c => c.data);
                            if (newData.length === 0) {
                                DevExpress.ui.notify("No valid data to save", "info", 2000);
                                return;
                            }
                            $.ajax({
                                url: "{{ route('create.task.crud.newS') }}",
                                type: "POST",
                                contentType: "application/json",
                                data: JSON.stringify({ tasks: newData }),
                                success: function () {
                                    DevExpress.ui.notify("Data saved successfully", "success", 2000);
                                    window.location.href = "{{ route('create.task.listS') }}";
                                },
                                error: function (xhr) {
                                    let errorMessage = xhr.responseText || "Unknown error";
                                    DevExpress.ui.notify("Failed to save data: " + errorMessage, "error", 2000);
                                },
                            });
                        }
                    }
                },
                {
                    widget: "dxButton",
                    location: "after",
                    options: {
                        icon: "chevrondoubleright",
                        hint: "Close",
                        onClick: function () {
                            window.location.href = "{{ route('create.task.listS') }}";
                        }
                    }
                }
            ]
        }
    });
</script>
@endsection
