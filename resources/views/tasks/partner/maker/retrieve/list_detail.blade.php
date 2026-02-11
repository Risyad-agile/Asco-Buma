@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8" style="margin-top: 20px; margin-bottom:20px">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" data-autohide="true" >
        <div class="toast-header">
            <strong class="me-auto">Account Style Missing</strong>
            <small>ASRI-CONNECT</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        @if ($notupdate === 0)
            <div class="toast-body">
                Account Style has been fix, you can continue to submit and create data for upload
            </div>
        @else
            <div class="toast-body">
                This data can't uploaded to the Envizi because some or all Account Data Style missing and can't be found
                please fix it from data source or using mapping data to fix the issue
            </div>
        @endif
    </div>
    </div>
</div>
<form method="POST" action="{{route('partner.task.maker.retrieve.data.mapping')}}">
    @csrf 
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="taskid" value="{!!$taskid!!}" class="form-control" placeholder="Upload Task ID" hidden>
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
    const notupdate ={!!$notupdate!!};
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
                return $("<div class='long-title'><h3>List Of Retrieving Data</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "rowfield",
                    hint: 'Mapping Account Style',
                    useSubmitBehavior: true,
                    onClick: function(e) {  
                        $("#txtstate").val("MAPPING");      
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "check",
                    hint: 'Submit and Update task',
                    useSubmitBehavior: true,
                    onClick: function(e) {     
                        if(notupdate!=0){
                            Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please mapping Account Style first before continue...",
                              footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                          })
                          e.preventDefault();
                          return false;  
                        }   
                        $("#txtstate").val("SUBMIT"); 
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
                        window.location = "{{route('partner.task.maker.retrieve.list.task')}}";
                    }
                }
            }]
        });
      $("#gridContainer").dxDataGrid({
          dataSource: accstyleimports,
          keyExpr: "id",
          columnWidth: 150,
          showBorders: true,
          scrolling: {
            mode: "standard"
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
