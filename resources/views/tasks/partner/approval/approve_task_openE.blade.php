@extends('layouts.master')

@section('content') 
    <div id="taskDetail" style="margin-top: 10px;"></div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
<script type="text/javascript">
    // Setup untuk CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var taskid = {!! $task->id !!};
    var transno = {!! json_encode($task->trans_no) !!};
   

    // Data Source untuk Grid
    var gridDataSource = new DevExpress.data.DataSource({
        store: new DevExpress.data.CustomStore({
            key: "id",
            load: function () {
                return $.getJSON("{{ URL::to('partner-manager/approve/task/crud/readE') }}/" + taskid+"/"+transno);
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
                enabled: false,
                fileName: "TaskContain",
            },
            allowColumnResizing: true,
            editing: {
                mode: "single",
                allowUpdating: false,
                allowAdding: false,
                allowDeleting:false, 
                useIcons: true,
            },
            searchPanel: {
                visible: false,
                highlightCaseSensitive: true,
            },
            toolbar: {
                items: [
                    {
                        location: "before",
                        template: function () {
                            return $("<div>")
                                .text("Task Detail  ("+ transno+")" )
                                .css({
                                    "font-size": "25px",
                                    "font-weight": "bold",
                                    "font-family": "Arial, sans-serif",
                                    "margin-right": "20px"
                                });
                        }
                    },{
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
                                                        task_state: "Ready to Submit",
                                                        reason: reason // Kirim alasan ke server
                                                    },
                                                    success: function (response) {
                                                        Swal.fire({
                                                            title: "Updated!",
                                                            text: "Task status has been updated.",
                                                            icon: "success"
                                                        }).then(() => {
                                                            window.location.href = "{{ route('approve.task.listE') }}";
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
                    },{
                        location: "after",
                        widget: "dxButton",
                        options: {
                            icon: "check",
                            hint: "Approve and Submit", 
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
                                    text: "This task will be marked as Approved and the data will be processed.",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonText: "Yes, Process!",
                                    cancelButtonText: "No, cancel!",
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $.ajax({
                                            url: "{{ route('update.task.stateApprovalE') }}",
                                            type: "POST",
                                            data: {
                                                task_id: id, 
                                                task_state: "Approved",
                                            },
                                            success: function (response) {
                                                Swal.fire({
                                                    title: "Updated!",
                                                    text: "Task status has been updated.",
                                                    icon: "success", 
                                                }).then(() => {
                                                    window.location.href = "{{ route('approve.task.listE') }}";
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
                                window.location.href = "{{ route('approve.task.listE') }}";
                            }
                        }
                    }
                ]
            }, 
            columns: [
                { dataField: "id", caption: "ID", visible: false, allowEditing: false },
                { dataField: "task_id", caption: "Tasks ID", visible: false, allowEditing: false },
                { dataField: "organization_name", caption: "Organization", visible: false, allowEditing: false },
                { dataField: "location_name", caption: "Location", width: 200 },
                { dataField: "acc_style_mtra_caption", caption: "Description", allowEditing: false, width : 250  },
                { dataField: "acc_style_caption", caption: "Account Style", allowEditing: false, width:220  }, 
                { dataField: "acc_number", caption: "Account Number", witdh : 100 }, 
                { dataField: "acc_supplier", caption: "Supplier", width: 100 },
                { dataField: "record_date_start", caption: "Record Start", alignment: "center", dataType: "datetime", format: 'dd-MM-yyyy', width:110 },
                { dataField: "record_date_end", caption: "Record End", alignment: "center", dataType: "datetime", format: 'dd-MM-yyyy', width:110 },
                { dataField: "acc_data_qty", caption: "Qty", dataType:"number", format: "fixedPoint", width:50, alignment: "center", width:50},
                { dataField: "acc_data_tot_cost", caption: "Total Cost (incl. Tax) in Local Currency", dataType: "number", format: "fixedPoint", width:120 },
            ]
        }).dxDataGrid("instance");
    });
     
</script>
@endsection