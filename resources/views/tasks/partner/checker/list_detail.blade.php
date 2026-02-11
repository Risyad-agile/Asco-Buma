@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="gridContainer"></div> 

{{-- add reason for rejected dialog --}}
<div class="modal fade" id="mdlRejectReason" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary">
            <h3 id="mdlRejectReasonTitle" class="modal-title"><span class="badge badge-primary">Reason</span></h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="form-container" class="first-group">
                <div id="form"></div>
            </form>
        </div>
        </div>
    </div>
</div>
{{-- end reason for rejected dialog --}}

@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.fn.serializeObject = function(){
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
    const taskid ="{!!$task->id!!}";
    const accdataloadimport = {!! $accdataloadimport !!};
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
                return $("<div class='long-title'><h3>List Of Process Account</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "check",
                    hint: 'Task Checked',
                    onClick: function(e) {      
                    Swal.fire({
                        title: 'Confirmation',
                        text: "Are you sure already checked and agree to continue processing data...??",
                        type: 'warning',
                        showCancelButton: true,
                        // confirmButtonColor: '#3085d6',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, Continue',
                        cancelButtonText: 'No',
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                type: "PUT",
                                url: "{{URL::to('partner-supervisor/task/checker/continue')}}"+"/"+taskid,
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                success: function (data) {
                                    if(data.code != 200) {
                                        Swal.fire({
                                            icon: data.status,
                                            title: "Validation Error",
                                            text: data.message,
                                            footer: '<a href="">Why do I have this issue?</a>'
                                        })
                                    }else{
                                        Swal.fire({
                                            icon: data.status,
                                            title: "Checked",
                                            text: data.message,
                                        }).then((result) => {
                                            window.location.href = '{{route('partner.task.checker.list.task')}}';
                                        });
                                    }
                                    return false;
                                },    
                                error: function(data) {
                                    swal({
                                        title: "Validation Error",
                                        icon: data.status,
                                        text: data.message,
                                        value: true,
                                        visible: true,
                                        className: "",
                                        closeModal: true,
                                    });
                                    return false;
                                }            
                            });
                        }
                    })
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "deletetable",
                    hint: 'Task Checked and Reject',
                    onClick: function(e) {      
                        $('#mdlRejectReason').modal('show'); 
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Close',
                    onClick: function(e) {      
                        window.location = "{{route('partner.task.checker.list.task')}}";
                    }
                }
            }]
        });
      $("#gridContainer").dxDataGrid({
          dataSource: accdataloadimport,
          keyExpr: "id",
          columnWidth: 150,
          showBorders: true,
          scrolling: {
            columnRenderingMode: "virtual"
          },
          "export": {
                enabled: true, 
            },
          allowColumnResizing: true,
          searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
          selection: {
            mode: "single"
          },
          paging: {
              pageSize: 15,
          },
          onExporting(e) {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet('Records to load');
            const filename ='AcountDataLoadChecker'+'.xlsx';

            DevExpress.excelExporter.exportDataGrid({
                component: e.component,
                worksheet,
                autoFilterEnabled: true,
            }).then(() => {
                workbook.xlsx.writeBuffer().then((buffer) => {
                saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'AcountDataLoadChecker.xlsx');
                });
            });
            },
          columns: [{
                dataField: "id",
                caption: "ID",
                visible:false,
            },{
                dataField: "organization_name",
                caption: "Organization",
            },{
                dataField: "location_name",
                caption: "Location",
            },{
                dataField: "acc_style_caption",
                caption: "Account Style Caption",
            },{
                dataField: "acc_number",
                caption: "Account Number",
            },{
                dataField: "acc_reference",
                caption: "Account Reference",
            },{
                dataField: "acc_supplier",
                caption: "Account Supplier",
            },{
                dataField: "record_date_start",
                caption: "Record Start YYYY-MM-DD",
                dataType: "datetime",
                format:'yyyy-MM-dd',
            },{
                dataField: "record_date_end",
                caption: "Record End YYYY-MM-DD",
                dataType: "datetime",
                format:'yyyy-MM-dd',
            },{
                dataField: "acc_data_qty",
                caption: "Quantity",
                dataType:"number",
                format: "fixedPoint",
            },{
                dataField: "acc_data_tot_cost",
                caption: "Total cost (incl. Tax) in local currency",
                dataType:"number",
                format: "fixedPoint",
            },{
                dataField: "record_reference",
                caption: "Record Reference",
            },{
                dataField: "record_inv_no",
                caption: "Record Invoice Number",
            },{
                dataField: "record_quality",
                caption: "Record Data Quality",
              },
          ],
      });
      $("#form").dxForm({
        colCount:1,
        items: [{
            dataField: "reject_reason",
            label:{
                text:"Reason for Rejection",
            },
            editorType: 'dxTextArea',
            editorOptions: {
                height: 125,
            },
        },{
            itemType: "button",
            horizontalAlignment: "right",
            buttonOptions: {
                text: "Continue",
                type: "success",
                // useSubmitBehavior: true,
                onClick: function(e) {      
                    $('#mdlRejectReason').modal('hide');
                    var form =$('#form-container').serializeObject();
                    var reject_reason=form['reject_reason'];
                    if(reject_reason==""){
                        swal({
                            title: "Warning",
                            icon: "warning",
                            text: "Please Fill Reason for Rejection",
                            value: true,
                            visible: true,
                            className: "",
                            closeModal: true,
                        });
                        return false;
                    }
                    rejecttask(taskid,reject_reason)
                }
            }
        },]
  });
  });

  function rejecttask(taskid,reject_reason) {
    Swal.fire({
        title: 'Confirmation',
        text: "Are you sure want to Rejected these Data...?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{route('partner.task.checker.reject')}}",
                method: "POST",
                data: JSON.stringify({taskid,reject_reason}),                
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (data) {
                    if(data.code != 200) {
                        Swal.fire({
                            icon: data.status,
                            title: "Validation Error",
                            text: data.message,
                            footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                        })
                    }else{
                        Swal.fire({
                            icon: data.status,
                            title: "Succesful Send",
                            text: data.message,
                        }).then((result) => {
                            window.location.href = '{{route('partner.task.checker.list.task')}}';
                        });
                    }
                    return false;
                },    
                error: function(data) {
                    swal({
                        title: "Validation Error",
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                    return false;
                }            
            });
        }
    }) 
}
</script>
@endsection
