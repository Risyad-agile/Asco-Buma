@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('partner.task.maker.retrieve.data.mapping')}}">
    @csrf 
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="taskid" value="{!!$task->id!!}" class="form-control" placeholder="Upload Task ID" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" placeholder="Upload Task ID" hidden>
</form>
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
    const accstyleimports = {!! $accstyleimports !!};
    const taskid="{!!$task->id!!}";
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#toolbar").dxToolbar({
        items: [{
            location: 'before',
            locateInMenu: 'never',
            template: function() {
                return $("<div class='long-title'><h3>Task Contain</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "check",
                    hint: 'Submit and Create task', 
                    onClick: function(e) {     
                        Swal.fire({
                            title: 'Confirmation',
                            text: "Are you sure want to submited these data for check...??",
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
                                    url: "{{URL::to('partner-user/task/maker/created/')}}"+"/"+taskid,
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
                                                window.location.href = '{{route('partner.task.maker.upload.list.task')}}';
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
                    icon: "close",
                    hint: 'Close',
                    onClick: function(e) {      
                        window.location = "{{route('partner.task.maker.upload.list.task')}}"; 
                    }
                }
            }]
        });
      $("#gridContainer").dxDataGrid({
          dataSource: accstyleimports,
          keyExpr: "id", 
          columnAutoWidth:true,
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
              pageSize: 21,
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
      
});
</script>
@endsection
