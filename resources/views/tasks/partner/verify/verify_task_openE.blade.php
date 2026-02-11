@extends('layouts.master')

@section('content') 
    <div id="taskDetail" style="margin-top: 10px;"></div>
@endsection

@section('script')
<script type="text/javascript">
    // Setup untuk CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var taskid = {!! $task->id !!};
    var transno = {!! json_encode($task->trans_no) !!};
    // Data Source untuk Lokasi
    var locationDataSource = new DevExpress.data.CustomStore({
        loadMode: "raw",
        load: function () {
            return $.getJSON("{{ route('partner.getLocations') }}");
        }
    });
    // Data Source untuk Account Number
    var isUpdating = false;
    var accountNumberDataSource = new DevExpress.data.CustomStore({
    loadMode: "raw",
    load: function () {
            return $.ajax({
                url: "{{ route('partner.getAccountNumber') }}",
                type: "GET",
                dataType: "json"
            });
        }
    });
    

    // Data Source untuk Account Styles
    var isUpdating = false;
    var accountStyleClientDataSource = new DevExpress.data.CustomStore({
    loadMode: "raw",
    load: function () {
            return $.ajax({
                url: "{{ route('partner.getAccountStylesClientE') }}",
                type: "GET",
                dataType: "json"
            }).done(function(data) {
                 console.log(data);  // Cek response dari server
            });
        }
    }); 

    // Data Source untuk Grid
    var gridDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            load: function () {
                return $.getJSON("{{ URL::to('partner-supervisor/verify/task/crud/readE') }}/" + taskid+"/"+transno);
            },
            insert: function (values) {
                console.log("Insert Values:", values); // Debugging
                if (!values.acc_data_state) {
                    values.acc_data_state = '1'; 
                }
                return $.ajax({
                    url: "{{ route('verify.task.crud.insertRow') }}",
                    type: "POST",
                    data: JSON.stringify({data:values}),
                    contentType: "application/json",
                    success: function (response) {
                        DevExpress.ui.notify("Data successfully added", "success", 2000);
                    },
                    error: function (xhr) {
                        let errorMessage = xhr.responseText ? xhr.responseText : "Unknown error";
                        DevExpress.ui.notify("Insert failed: " + errorMessage, "error", 2000);
                    }
                });
            },
            update: function (key, values) {
                return $.ajax({
                    url: "{{ url('partner-supervisor/verify/task/crud/update') }}/" + key,
                    type: "PUT",
                    contentType: "application/json",
                    data: JSON.stringify(values),
                    dataType: "json",
                    success: function (response) {
                        DevExpress.ui.notify("Data successfully updated", "success", 2000);
                        window.location.href = "{{ route('verify.task.listE') }}";
                    },
                    error: function (xhr) {
                        let errorMessage = xhr.responseText ? xhr.responseText : "Unknown error";
                        DevExpress.ui.notify("Update failed: " + errorMessage, "error", 2000);
                    }
                });
            },
            remove: function (key) {
                return $.ajax({
                    url: "{{ url('partner-supervisor/verify/task/crud/updateDel') }}/" + key,
                    type: "DELETE",
                    contentType: "application/json",
                    success: function () {
                        DevExpress.ui.notify("Data successfully deleted", "success", 2000);
                        gridDataSource.reload();
                    },
                    error: function (xhr) {
                        DevExpress.ui.notify("Delete failed: " + xhr.responseText, "error", 2000);
                    }
                });
            }
        })
    });




    // Data Grid DevExtreme
    $(function() {
        const dataGrid = $("#taskDetail").dxDataGrid({
            dataSource: gridDataSource,
            showBorders: true,
            paging: { pageSize: 18 },
            pager: {
                showPageSizeSelector: false,
                allowedPageSizes: [5, 10, 15],
                showInfo: true,
            },
            export: {
                enabled: true,
                fileName: "TaskContain",
            },
            allowColumnResizing: true,
            editing: {
                mode: "batch",
                allowUpdating: true,
                allowAdding: true,
                allowDeleting:true,
                selectTextOnEditStart: true,
                startEditAction: "click",
                useIcons: true,
            },
            searchPanel: {
                visible: false,
                highlightCaseSensitive: true,
            },
            toolbar: {
                items: [
                    {
                        location: "center",
                        template: function () {
                            return $("<div>")
                                .text("Task Verification - Edit ( "+ transno+" )" )
                                .css({
                                    "font-size": "25px",
                                    "font-weight": "bold",
                                    "font-family": "Arial, sans-serif",
                                    "margin-right": "20px"
                                });
                        }
                    },
                    "addRowButton", 
                    "saveButton",
                    "revertButton",
                    {
                        location: "after",
                        widget: "dxButton",
                        options: {
                            icon: "close",
                            hint: "Reject ( Return Process )",
                            useSubmitBehavior: false,
                            disabled: false,
                            onClick: function () {
                                var id = taskid;
                                if (!id) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Validation Error",
                                        text: "Please select a task first!"
                                    });
                                    return;
                                }

                                // Munculkan popup input reason
                                Swal.fire({
                                    title: "Enter Reason",
                                    input: "text",
                                    inputPlaceholder: "Enter reason for this process...",
                                    showCancelButton: true,
                                    confirmButtonText: "Next",
                                    cancelButtonText: "Cancel",
                                    inputValidator: (value) => {
                                        if (!value) {
                                            return "Reason cannot be empty!";
                                        }
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        let reason = result.value;

                                        // Konfirmasi sebelum update
                                        Swal.fire({
                                            title: "Are you sure?",
                                            text: "This task will be marked to before processing.",
                                            icon: "warning",
                                            showCancelButton: true,
                                            confirmButtonText: "Yes, submit it!",
                                            cancelButtonText: "No, cancel!",
                                        }).then((confirmResult) => {
                                            if (confirmResult.isConfirmed) {
                                                $.ajax({
                                                    url: "{{ route('update.task.stateDown') }}",
                                                    type: "POST",
                                                    data: {
                                                        task_id: id,
                                                        task_state: "Ready to Verify",
                                                        reason: reason // Kirim alasan ke server
                                                    },
                                                    success: function (response) {
                                                        Swal.fire({
                                                            title: "Updated!",
                                                            text: "Task status has been updated.",
                                                            icon: "success"
                                                        }).then(() => {
                                                            window.location.href = "{{ route('verify.task.listE') }}";
                                                        });
                                                    },
                                                    error: function () {
                                                        Swal.fire("Error!", "Failed to update task status.", "error");
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    }
                    ,{
                        location: "after",
                        widget: "dxButton",
                        options: {
                            icon: "check",
                            hint: "Submit to Approve", 
                            useSubmitBehavior: false,
                            disabled: false,                      
                            onClick: function () {
                                var id = taskid;
                                if (!id) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Validation Error",
                                        text: "Please select a task first!"
                                    });
                                    return;
                                }
                                
                                // Konfirmasi sebelum update
                                Swal.fire({
                                    title: "Are you sure?",
                                    text: "This task will be marked as Ready to Approve.",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, submit it!",
                                    cancelButtonText: "No, cancel!",
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $.ajax({
                                            url: "{{ route('update.task.stateUp') }}",
                                            type: "POST",
                                            data: {
                                                task_id: id, 
                                                task_state: "Ready to Approve",
                                            },
                                            success: function (response) {
                                                Swal.fire({
                                                    title: "Updated!",
                                                    text: "Task status has been updated.",
                                                    icon: "success"
                                                }).then(() => {
                                                    window.location.href = "{{ route('verify.task.listE') }}"; 
                                                });
                                            },
                                            error: function () {
                                                Swal.fire("Error!", "Failed to update task status.", "error");
                                            }
                                        });
                                    }
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
                                window.location.href = "{{ route('verify.task.listE') }}";
                            }
                        }
                    }
                ]
            },
            columns: [
                { dataField: "id", caption: "ID", visible: false, allowEditing: false },
                { dataField: "task_id", caption: "Tasks ID", visible: false, allowEditing: false },
                { dataField: "organization_name", caption: "Organization", visible: false, allowEditing: false },
                { dataField: "location_name", caption: "Location", width: 200, 
                    lookup: {
                        dataSource: locationDataSource,
                        valueExpr: "location_name",
                        displayExpr: "location_name"
                    },
                    validationRules: [{ type: "required" }]
                }, 
                { dataField: "acc_style_mtra_caption", caption: "Description",  allowEditing: true, width : 250,
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
                                var grid = $("#taskDetail").dxDataGrid("instance");
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
                { dataField: "acc_supplier", caption: "Supplier", width: 100 },
                { dataField: "record_date_start", caption: "Record Start", alignment: "center", dataType: "datetime", format: 'dd-MM-yyyy', width:110 },
                { dataField: "record_date_end", caption: "Record End", alignment: "center", dataType: "datetime", format: 'dd-MM-yyyy', width:110 },
                { dataField: "acc_data_qty", caption: "Qty", dataType:"number", format: "fixedPoint", width:50, alignment: "center", width:50},
                { dataField: "acc_data_tot_cost", caption: "Total Cost", dataType: "number", format: "fixedPoint", width:120 },
            ],
            onInitNewRow: function(e) {
                const now = new Date();
                e.data = {
                    task_id: taskid,
                    location_name: "",
                    acc_style_mtra_caption: "",
                    acc_style_caption: "",
                    acc_number: "",
                    acc_supplier: "",
                    record_date_start: new Date(now.getFullYear(), now.getMonth(), 1).toISOString(),
                    record_date_end: new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString(),
                    acc_data_qty: 0,
                    acc_data_tot_cost: 0.0
                };
            }
        }).dxDataGrid("instance");

        // Checkbox untuk "Select Text on Edit Start"
        $("#selectTextOnEditStart").dxCheckBox({
            value: true,
            text: "Select Text on Edit Start",
            onValueChanged: function(data) {
                dataGrid.option("editing.selectTextOnEditStart", data.value);
            }
        });
    });
</script>
@endsection