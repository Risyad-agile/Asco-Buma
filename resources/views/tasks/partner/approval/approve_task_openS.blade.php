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
                return $.getJSON("{{ URL::to('partner-manager/approve/task/crud/readS') }}/" + taskid+"/"+transno);
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
                                                            window.location.href = "{{ route('approve.task.listS') }}";
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
                                            url: "{{ route('update.task.stateApprovalS') }}",
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
                                                    window.location.href = "{{ route('approve.task.listS') }}";
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
                                window.location.href = "{{ route('approve.task.listS') }}";
                            }
                        }
                    }
                ]
            }, 
            columns: [             
                {   dataField: "location_name", caption: "Location", width: 180,  },
                {   dataField: "acc_style_mtra_caption", caption: "Account Style", allowEditing : true, width: 180,  },
                {   dataField: "acc_style_caption", caption: "Account Style Note", allowEditing: false, width:220  }, 
                {   dataField: "record_date_start", caption: "Date Start", dataType: "date", format: "yyyy-MM-dd", alignment : "center", width: 90 },
                {   dataField: "record_date_end", caption: "Date End", dataType: "date", format: "yyyy-MM-dd", alignment : "center", width: 90 }, 
                {   dataField: "csr_male", caption: "Male", dataType: "number", format: "fixedPoint",  alignment : "center", width: 55},
                {   dataField: "csr_female", caption: "FMale", dataType: "number", format: "fixedPoint", alignment : "center", width: 55 },
                {   dataField: "csr_less_30", caption: "<30", dataType: "number", format: "fixedPoint", alignment : "center", width: 55,
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
                        .text("HS") // tetap H-S di tampilan
                        .attr("title", "High School") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_junior_high_school", caption: "JHS", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("JHS") // tetap J-HS di tampilan
                        .attr("title", "Junior High School") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_elementary_school", caption: "ES", dataType: "number", format: "fixedPoint", alignment : "center", width: 55,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("ES") // tetap ES di tampilan
                        .attr("title", "Elementary School") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_eduction_other", caption: "OS", dataType: "number", format: "fixedPoint", alignment : "center", width: 55,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("OS") // tetap OS di tampilan
                        .attr("title", "Other School") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_islam", caption: "Ism", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Ism") // tetap Ism di tampilan
                        .attr("title", "Islam") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_budha", caption: "Bdh", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Bdh") // tetap Bdh di tampilan
                        .attr("title", "Budha") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_hindu", caption: "Hdu", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Hdu") // tetap Hdu di tampilan
                        .attr("title", "Hindu") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_katolik", caption: "Kat", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Kat") // tetap Kat di tampilan
                        .attr("title", "Katolik") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_kristen", caption: "Krs", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("Krs") // tetap Krs di tampilan
                        .attr("title", "Kristen") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
                { dataField: "csr_religion_other", caption: "ORg", dataType: "number", format: "fixedPoint", alignment : "center", width: 50,
                    headerCellTemplate: function (header, info) {
                        $("<div>")
                        .text("ORg") // tetap ORg di tampilan
                        .attr("title", "Other Religion") // ini yang jadi hint
                        .appendTo(header);
                    }
                 },
            ]
        }).dxDataGrid("instance");
    });
     
</script>
@endsection