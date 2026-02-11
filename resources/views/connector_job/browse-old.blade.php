@extends('layouts.master')
@section('content')
<form method="POST">
    @csrf
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="id" hidden>
</form>
@endsection

@section('script')
<script type="text/javascript">
$(function(){ 
    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    var jobs = @json($jobs);
    var sources = @json($sources);
    var companies = @json($companies);
    var companySelectBox = null;

    // -----------------------------------------------------
    // 1️⃣ BUILD GRID DULU (BIAR GRID SUDAH ADA SEBELUM TOOLBAR)
    // -----------------------------------------------------
    var grid = $("#gridContainer").dxDataGrid({
        dataSource: jobs,
        keyExpr: "id",
        showBorders: true,
        searchPanel: { visible: true, width: 300, placeholder: "Search job..." },
        selection: { mode: "single" },
        paging: { pageSize: 10 },

        columns: [
            { dataField: "id", caption: "ID", visible: false },

            // filter
            { dataField: "comp_id", caption: "Company", visible: false },

            { dataField: "company.comp_name", caption: "Company Name", width: 150 },
            { dataField: "source.conn_source_name", caption: "Connector Name", width: 220 },
            { dataField: "job_name", caption: "Job Name", width: 200 },
            { dataField: "schedule_type", caption: "Schedule Type", width: 120 },
            { dataField: "schedule_time", caption: "Time", width: 100 },
            { dataField: "days_of_week", caption: "Days of Week", width: 150 },

            {
                dataField: "is_active",
                caption: "Status",
                width: 90,
                alignment: "center",
                cellTemplate: function(container, options) {
                    const icon = options.value ? "✅" : "❌";
                    const color = options.value ? "green" : "red";
                    $("<span>").text(icon).css("color", color).appendTo(container);
                }
            },

            { dataField: "last_run_at", caption: "Last Run", width: 160 },
            { dataField: "next_run_at", caption: "Next Run", width: 160 },
        ],

        onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            if(data) $("#txtid").val(data.id);
        }

    }).dxDataGrid("instance");



    // -----------------------------------------------------
    // 2️⃣ BARU BIKIN TOOLBAR — aman karena grid SUDAH ADA
    // -----------------------------------------------------
    $("#toolbar").dxToolbar({
        items: [

            // ---------------------------
            // SELECT COMPANY FILTER
            // ---------------------------
            {
                location: "before",
                template: function() {

                    var $container = $("<div>").css({
                        display: "inline-block",
                        marginRight: "10px"
                    });

                    var defaultCompany = companies.length > 0 ? companies[0] : null;

                    var selectBox = $("<div>").appendTo($container).dxSelectBox({
                        dataSource: companies,
                        displayExpr: "comp_name",
                        valueExpr: "comp_id",
                        placeholder: "Filter by Company...",
                        width: 260,
                        showClearButton: false,
                        value: defaultCompany ? defaultCompany.comp_id : null,

                        onValueChanged: function(ev) {
                            var v = ev.value;
                            if(v) {
                                grid.filter(["comp_id", "=", v]);
                            } else {
                                grid.clearFilter();
                            }
                        }
                    }).dxSelectBox("instance");

                    companySelectBox = selectBox;

                    if (defaultCompany) {
                        grid.filter(["comp_id", "=", defaultCompany.comp_id]);
                    }

                    return $container;
                }
            },

            // Title
            {
                location: 'center',
                template: () => $("<div class='long-title'><h3>API Job Schedule</h3></div>")
            },


            // CREATE JOB
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "plus",
                    hint: 'Create New Job',
                    onClick: function() {
                        var compId = companySelectBox ? companySelectBox.option("value") : null;
                        if(!compId){
                            Swal.fire("Warning", "Please select a company first!", "warning");
                            return;
                        }
                        window.location = "{{ route('connector_job.create') }}?comp_id=" + compId;
                    }
                }
            },

            // EDIT JOB
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "edit",
                    hint: 'Edit Job',
                    onClick: function() {
                        const txtid = $("#txtid").val();
                        if(!txtid){
                            Swal.fire("Error", "Please select a job first!", "error");
                            return;
                        }
                        window.location = "{{ url('asri-core/connector-job/edit') }}/" + txtid;
                    }
                }
            },

            // RUN FETCH
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "download",
                    hint: "Run Fetch Job (Client → Local DB)",
                    onClick: function() {
                        const txtid = $("#txtid").val();
                        if(!txtid){
                            Swal.fire("Warning", "Please select a job first.", "warning");
                            return;
                        }

                        $.post("{{ route('connector_job.runFetch') }}", { id: txtid })
                            .done((res) => Swal.fire("Success", res.message, "success"))
                            .fail(() => Swal.fire("Error", "Failed to run fetch job", "error"));
                    }
                }
            },

            // RUN PUSH
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "upload",
                    hint: "Run Push Job (Local DB → Envizi S3)",
                    onClick: function() {
                        const txtid = $("#txtid").val();
                        if(!txtid){
                            Swal.fire("Warning", "Please select a job first.", "warning");
                            return;
                        }

                        $.post("{{ route('connector_job.runPush') }}", { id: txtid })
                            .done((res) => Swal.fire("Success", res.message, "success"))
                            .fail(() => Swal.fire("Error", "Failed to run push job", "error"));
                    }
                }
            },

            // CHANGE ACTIVE STATUS
            {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "sorted",
                    hint: "Change Active Status",
                    onClick: function () {

                        const selected = grid.getSelectedRowsData();
                        if (selected.length === 0) {
                            Swal.fire("Warning", "Please select a job first.", "warning");
                            return;
                        }

                        const rowData = selected[0];
                        const id = rowData.id;

                        $.post("{{ route('connector_job.setStatus') }}", { id: id })
                        .done((response) => {
                            const newStatus = response.new_status ? 1 : 0;
                            const rowIndex = grid.getRowIndexByKey(id);
                            if (rowIndex >= 0) {
                                grid.cellValue(rowIndex, "is_active", newStatus);
                            }
                            Swal.fire("Success", "Status updated to: " + (newStatus ? "Active" : "Inactive"), "success");
                        })
                        .fail(() => {
                            Swal.fire("Error", "Failed to update status.", "error");
                        });
                    }
                }
            },

            // CLOSE
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "close",
                    hint: 'Close',
                    onClick: function() {
                        window.location = "{{ route('home') }}";
                    }
                }
            }

        ]
    });

});
</script>
@endsection
