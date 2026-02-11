@extends('layouts.master')
@section('content')
    <div id="toolbar"></div>
    <div id="gridProduk"></div>
@endsection

@section('script')
<script type="text/javascript">
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
            return $("<div class='long-title'><h3>Saldo Stok Produk Kategori {!! $storename !!}</h3></div>");
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
$(function(){
    $("#gridProduk").dxDataGrid({
        dataSource: {!! $products !!},
        keyExpr: "prodcat_id",
        showBorders: true,
        allowColumnResizing: true,
        "export": {
            enabled: true,
            fileName: "stock_value",
        },
        columnChooser: {
            enabled: true
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                dataField: "prodcat_id",
                caption: "Product ID",
                visible:false,
                width:100,
            },{
                dataField: "prodcat_desc",
                caption: "Kategori",
                visible:true, 
            },{
                dataField: "product_stock",
                caption: "Rata Rata Stok",
            },{
                dataField: "product_buy_price",
                caption: "Nilai Stok Beli",
                dataType:"number",
                format: "fixedPoint",
                visible:true,
            },{
                dataField: "product_price",
                caption: "Nilai Stok Jual",
                dataType:"number",
                format: "fixedPoint",
                visible:true,
            },
        ],
        sortByGroupSummaryInfo: [{
            summaryItem: "count"
        }],
        summary: {
            totalItems: [{
                column: "prodcat_id",
                summaryType: "count",
                displayFormat: "Items {0}",
            },{
                column: "product_buy_price",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",
            },{
                column: "product_price",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",  
            }]
        },
        onExporting(e) {
          const workbook = new ExcelJS.Workbook();
          const worksheet = workbook.addWorksheet('SupplierProducts');
          const storename ="{!!$storename!!}";
          const tglreport ="{!!$tglreport!!}";
          const namafile = "Saldo_Stock_Product_Category_"+tglreport+".xlsx"; 
          DevExpress.excelExporter.exportDataGrid({
            component: e.component,
            worksheet,
            topLeftCell: { row: 6, column: 1 },
          }).then((cellRange) => {
            // header
            const headerRow = worksheet.getRow(2);
            headerRow.height = 30;
            worksheet.mergeCells(2, 1, 2, 8);
            headerRow.getCell(1).value = 'Laporan Saldo Stok Produk Kategori';
            headerRow.getCell(1).font = { name: 'Segoe UI Light', size: 22 };
            headerRow.getCell(1).alignment = { horizontal: 'center' };

            const storeRow =worksheet.getRow(3);
            storeRow.getCell(1).value = "Store";
            storeRow.getCell(2).value = storename;
            storeRow.font = { name: 'Segoe UI Light', size: 16 };
            storeRow.alignment = { horizontal: 'left' };

            const dateRow =worksheet.getRow(4);
            dateRow.getCell(1).value = "Periode";
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
    });
});
</script>
@endsection
