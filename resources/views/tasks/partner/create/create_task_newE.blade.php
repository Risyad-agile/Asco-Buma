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
                url: "{{ route('partner.getLocations') }}", // Sesuaikan dengan route untuk mengambil data lokasi
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
                url: "{{ route('partner.getAccountStylesClientE') }}",
                type: "GET",
                dataType: "json"
            }).done(function(data) {
                // console.log(data);  // Cek response dari server
            });
        }
    });

    var gridDataSource = new DevExpress.data.DataSource({
        store: {
            type: "array",
            key: "id",
            insert: function (values) {
                // Pastikan acc_data_state ada di dalam values yang dikirimkan
                if (!values.acc_data_state) {
                    values.acc_data_state = ''; // atau nilai default lain yang sesuai
                }
                
                return $.ajax({
                    url: "{{ route('create.task.crud.newE') }}", // Pastikan URL sesuai dengan route yang benar
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ data: values }), // Mengirim data sesuai dengan format yang diterima controller
                    success: function (response) {
                        console.log("Response sukses:", response);
                        DevExpress.ui.notify("Data successfully added", "success", 2000);
                    },
                    error: function (xhr) {
                        console.log("Response gagal:", xhr);
                        let errorMessage = xhr.responseText ? xhr.responseText : "Unknown error";
                        DevExpress.ui.notify("Insert failed: " + errorMessage, "error", 2000);
                    },
                });
            }

        }
    });

    $("#taskNew").dxDataGrid({
        dataSource: gridDataSource,
        showBorders: true,
        paging: {
            pageSize: 18,
        },
        pager: {
            showPageSizeSelector: false,
            allowedPageSizes: [5, 10, 15],
            showInfo: true,
        },
        allowColumnResizing: true,
        editing: {
            mode: "batch",
            allowUpdating: true,
            allowAdding: true,
            allowDeleting: true,
            useIcons: true,
        },
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true,
        },
        onEditorPreparing: function (e) {
            if (e.parentType === "dataRow" && e.editorName === "dxTextBox") {
                e.editorOptions.onKeyDown = function (args) {
                    if (args.event.key === "Enter") {
                        args.event.preventDefault();
                        let dataGrid = $("#taskNew").dxDataGrid("instance");
                        let nextColumn = getNextEditableColumn(e.column.index, dataGrid);
                        if (nextColumn !== null) {
                            dataGrid.editCell(e.row.rowIndex, nextColumn.index);
                        } else {
                            dataGrid.editCell(e.row.rowIndex, 0);
                        }
                    }
                };
            }
        },
        columns: [
            {   dataField: "id", caption: "Location",  width : 200, 
                lookup: {
                    dataSource: locationDataSource,
                    valueExpr: "id", // Ganti dengan field ID lokasi dari database
                    displayExpr: "location_name" // Ganti dengan field nama lokasi dari database
                },
                validationRules: [{ type: "required" }]
            }, 
            {   dataField: "acc_style_mtra_caption", caption: "Description", allowEditing: true, width : 250,
                lookup :{
                    dataSource : accountStyleClientDataSource,
                    valueExpr : "acc_style_mtra_caption",
                    displayExpr : "acc_style_mtra_caption" 
                },
                editorOptions: {
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
            { dataField: "acc_style_caption", caption: "Account Style", allowEditing: false, width:220  },
            { dataField: "acc_number", caption: "Account Number", allowEditing: true, width: 180 },
            { dataField: "acc_supplier", caption: "Supplier", width:100 },
            { 
                dataField: "record_date_start", 
                caption  : "Record Start", 
                alignment : "center",
                dataType: "date",
                width:110,
                format: "yyyy-MM-dd" 
            },
            { 
                dataField: "record_date_end", 
                caption  : "Record End", 
                alignment : "center",
                dataType : "date", 
                width:110,
                format: "yyyy-MM-dd" 
            },
            { dataField: "acc_data_qty", caption: "Qty", dataType:"number", format: "fixedPoint", width:50, alignment: "center"},
            { dataField: "acc_data_tot_cost", caption: "Cost", dataType: "number", format: "fixedPoint", width:120 },
        ],
        toolbar: {
            items: [
                {
                    location: "before",
                    template: function () {
                        return $("<div>")
                            .text("Task : Data Capture ")
                            .css({
                                "font-size": "25px",
                                "font-weight": "bold",
                                "font-family": "Arial,san-serif",
                                "margin-right": "20px"
                            });
                    }
                }, 
                "addRowButton",
                {
                    location: "after",
                    widget: "dxButton",
                    options: {
                        icon: "fas fa-retweet",
                        hint: "Synchronize",
                        onClick: function () {


                            $.ajax({
                                url: "/sync-from-appclient", 
                                method: "POST",
                                contentType: "application/json", 
                                success: function (response) { 
                                      
                                    var data = response.data || [];
                                    var notFound = response.not_found_locations || [];

                                    if (data.length === 0) {
                                        DevExpress.ui.notify("DATA NOT FOUND !", "info", 2000);
                                    } 

                                    var gridInstance = $("#taskNew").dxDataGrid("instance");
                                    if (gridInstance) {
                                        gridInstance.option("dataSource", data);
                                    } else {
                                        console.error("❌ DataGrid instance not found!");
                                    }

                                    if (notFound.length > 0) {
                                        DevExpress.ui.notify(
                                            "⚠️ Ada lokasi tidak ditemukan: " + notFound.join(', '),
                                            "warning",
                                            4000
                                        );
                                    }
                                },
                                error: function (error) {
                                    console.log("❌ Error tarik data:", error);
                                }
                            });
                        }
                    }
                },
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
                "revertButton",
                {
                    widget: "dxButton",
                    location: "after",
                    options: {
                        icon: "save",
                        hint: "Save Task",
                        onInitialized: function(e) {
                            saveButtonInstance = e.component; // Simpan instance tombol agar bisa di-disable/enable
                        },
                        onClick: function () {

                            var grid = $("#taskNew").dxDataGrid("instance");

                            var changes = grid.option("editing.changes");

                            if (!changes || changes.length === 0) {
                                DevExpress.ui.notify("No data to save", "info", 2000);
                                return;
                            }

                            var newData = changes
                                .filter(c => c.type !== "remove") // Pastikan hanya data yang ditambah/diedit
                                .map(c => c.data);

                            if (newData.length === 0) {
                                DevExpress.ui.notify("No valid data to save", "info", 2000);
                                return;
                            }

                            saveButtonInstance.option("disabled", true);

                            $.ajax({
                                url: "{{ route('create.task.crud.newE') }}", // URL harus sesuai dengan route yang benar
                                type: "POST",
                                contentType: "application/json",
                                data: JSON.stringify({ tasks: newData }), // Mengirim data sesuai dengan format yang diterima controller
                                success: function (response) {
                                    DevExpress.ui.notify("Data saved successfully", "success", 2000);
                                    window.location.href = "{{ route('create.task.listE') }}";
                                    // grid.refresh();
                                },
                                error: function (xhr) {
                                    let errorMessage = xhr.responseText ? xhr.responseText : "Unknown error";
                                    DevExpress.ui.notify("Failed to save data: " + errorMessage, "error", 2000);
                                    saveButtonInstance.option("disabled", false); // Aktifkan kembali tombol jika gagal
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
                        hint: "Back",
                        onClick: function () {
                            window.location.href = "{{ route('create.task.listE') }}";
                        }
                    },
                }
            ]
        }
    });

    function getNextEditableColumn(currentIndex, grid) {
        let columns = grid.option("columns");
        for (let i = currentIndex + 1; i < columns.length; i++) {
            if (columns[i].allowEditing !== false) {
                return columns[i];
            }
        }
        return null;
    }

    
    function formatDate(dateString) {
        if (!dateString) return null;

        let date = new Date(dateString);
        if (isNaN(date.getTime())) {
            console.error("Invalid date format:", dateString);
            return null;
        }

        // Format ke "YYYY-MM-DD HH:MM:SS"
        let year = date.getFullYear();
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let day = String(date.getDate()).padStart(2, '0');
        let hours = String(date.getHours()).padStart(2, '0');
        let minutes = String(date.getMinutes()).padStart(2, '0');
        let seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

</script>
@endsection
