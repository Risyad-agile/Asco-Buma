@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
{{-- <div class="short-title"><h4>{!!$store->store_name!!}</h4></div> --}}
<form id="form-container" class="first-group">
      <div id="gridProduct"></div> 
</form>
@endsection

@section('script')
  <script type="text/javascript">
  $(function() {
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
            return $("<div class='long-title'><h3>CSR Migration List</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
                // useSubmitBehavior: true,
                onClick: function(e) {      
                    window.location = "{{route('home')}}";
                }
            }
        }]
    });
    var csrdata = {!! $csr !!}; 

    var dataGrid = $("#gridProduct").dxDataGrid({
            dataSource: csrdata,
            allowColumnReordering: true,
            allowColumnResizing: true,
            columnChooser:true,
            showBorders: true,
            searchPanel: {
                visible: true
            },
            paging: {
                pageSize: 10,
            },
            columnChooser: {
                enabled: true,
            },
            pager: {
                showPageSizeSelector: true,
                allowedPageSizes: [5, 10, 15],
                showInfo: true
            },
            "export": {
                enabled: true, 
            },
            onExporting(e) {
              const workbook = new ExcelJS.Workbook();
              const worksheet = workbook.addWorksheet('DailySales');
              const storename ="";
              const tglreport ="";
              const namafile = "DailySales_"+tanggalfile+".xlsx";

              DevExpress.excelExporter.exportDataGrid({
                component: e.component,
                worksheet,
                topLeftCell: { row: 6, column: 1 },
              }).then((cellRange) => {
                // header
                const headerRow = worksheet.getRow(2);
                headerRow.height = 30;
                worksheet.mergeCells(2, 1, 2, 8);
                headerRow.getCell(1).value = 'Laporan Penjualan Harian';
                headerRow.getCell(1).font = { name: 'Segoe UI Light', size: 22 };
                headerRow.getCell(1).alignment = { horizontal: 'center' };

                const storeRow =worksheet.getRow(3);
                storeRow.getCell(1).value = "Store";
                storeRow.getCell(2).value = storename;
                storeRow.font = { name: 'Segoe UI Light', size: 16 };
                storeRow.alignment = { horizontal: 'left' };

                const dateRow =worksheet.getRow(4);
                dateRow.getCell(1).value = "Tanggal";
                dateRow.getCell(2).value = tglreport;
                dateRow.font = { name: 'Segoe UI Light', size: 16 };
                dateRow.alignment = { horizontal: 'left' };
                
                // footer
                const footerRowIndex = cellRange.to.row + 2;
                const footerRow = worksheet.getRow(footerRowIndex);
                worksheet.mergeCells(footerRowIndex, 1, footerRowIndex, 8);

                footerRow.getCell(1).value = 'http://www.agile.co.id';
                footerRow.getCell(1).font = { color: { argb: 'BFBFBF' }, italic: true };
                footerRow.getCell(1).alignment = { horizontal: 'right' };
              }).then(() => {
                workbook.xlsx.writeBuffer().then((buffer) => {
                  saveAs(new Blob([buffer], { type: 'application/octet-stream' }), namafile);
                });
              });
              e.cancel = true;
            },
            columns: [
                {
                  caption: "Location",
                  dataField: "csr_location_name",
                },{
                  caption: "Account Style Caption",
                  dataField: "csr_account_style",
                },{
                  dataField:"csr_male",
                  caption: "Male",
                  format: "fixedPoint",  
                },{
                  dataField:"csr_female",
                  caption: "Female",
                  format: "fixedPoint",  
                },{
                  dataField:"csr_less_30",
                  caption: "< 30",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_between_30_50",
                  caption: ">30 <50",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_more_50",
                  caption: ">50",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_phd",
                  caption: "PHd",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_post_graduate",
                  caption: "Post Graduate",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_bachelor_degree",
                  caption: "Bachelor",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_high_school",
                  caption: "High School",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_junior_high_school",
                  caption: "Junior",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_elementary_school",
                  caption: "Elementary",
                  format: "fixedPoint", 
                  visible:false,
                },{
                  dataField:"csr_others_school",
                  caption: "Other School Level", 
                  format: "fixedPoint",
                  visible:false,
                  // width:100,
                },{
                  caption: "csr_budha",
                  caption: "Buddha",
                  format: "fixedPoint",
                  visible:false,
                },{
                  caption: "csr_hindu",
                  caption: "Hindu",
                  format: "fixedPoint",
                  visible:false,
                },{
                  dataField:"csr_islam",
                  caption: "Moslem",
                  format: "fixedPoint",
                  visible:false,
                  // width:100,  
                },{
                  caption: "csr_katolik",
                  caption: "Katolik",
                  format: "fixedPoint",
                  visible:false,
                  // width:150,
                },{
                  caption: "csr_kristen",
                  caption: "Kristen",
                  format: "fixedPoint",
                  visible:false,
                },{
                  caption: "csr_other_religion",
                  caption: "Other Religion",
                  format: "fixedPoint",
                  visible:false,
                },
            ],
            sortByGroupSummaryInfo: [{
                summaryItem: "count"
            }],
            summary: {
                groupItems: [{
                  column: "Jumlah",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Qty : {0}",
                    showInGroupFooter: true,
                },{
                    column: "Total",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Total : {0}",
                    showInGroupFooter: true,
                },{
                    column: "Margin",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Margin {0}",
                    showInGroupFooter: true,    
                }],
              totalItems: [{
                    column: "product_id",
                    summaryType: "count",
                    displayFormat: "Items {0}",
                },{
                    column: "Total",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Tot. Sales {0}",
                },{
                    column: "Margin",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Margin {0}",    
                },{
                    column: "Tax",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Tax {0}",    
                 },{
                    column: "Service",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Srv {0}",    
                }]
            }
        }).dxDataGrid("instance");
  });
  </script>
@endsection
