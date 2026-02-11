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
            return $("<div class='long-title'><h3>Saldo Stok Produk {!! $store->store_name !!}</h3></div>");
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
        keyExpr: "id",
        showBorders: true,
        allowColumnResizing: true,
        "export": {
            enabled: true, 
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
        groupPanel: {
            visible: true
        },
        columns: [
            {
                dataField: "id",
                caption: "Product ID",
                visible:false,
                width:100,
            },{
                dataField: "product_plu",
                caption: "PLU",
                visible:false, 
            },{
                dataField: "productcategory.prodcat_desc",
                caption: "Kategori",
                visible:true,
                // groupIndex: 0, 
            },{
                dataField: "brand.brand_name",
                caption: "Merek",
                visible:false,
            },{
                dataField: "product_barcode",
                caption: "Barcode",
                visible:false,
                width:120,
            },{
                dataField: "product_name",
                caption: "Deskripsi",
            },{
                dataField: "product_buy_price",
                caption: "Beli",
                dataType:"number",
                format: "fixedPoint",
                visible:true,
            },{
                dataField: "product_price",
                caption: "Jual",
                dataType:"number",
                format: "fixedPoint",
                visible:false,
            },{
                dataField: "product_stock",
                caption: "Stok",
                width:75,
            },{
                dataField: "harga_stok_beli",
                caption: "Nilai Stok Beli",
                dataType:"number",
                format: "fixedPoint",
                visible:true,
                calculateCellValue: function(rowData) {
                    var harga_beli=rowData.product_buy_price;
                    var jumlah=rowData.product_stock;
                    var harga_stok_beli=harga_beli*jumlah;
                    return harga_stok_beli;
                }
            },{
                dataField: "harga_stok_jual",
                caption: "Nilai Stok Jual",
                dataType:"number",
                format: "fixedPoint",
                visible:false,
                calculateCellValue: function(rowData) {
                    var harga_jual=rowData.product_price;
                    var jumlah=rowData.product_stock;
                    var harga_stok_jual=harga_jual*jumlah;
                    return harga_stok_jual;
                }
            },
        ],
        sortByGroupSummaryInfo: [{
            summaryItem: "count"
        }],
        summary: {
            groupItems: [{
                column: "product_desc",
                summaryType: "count",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Item : {0}",
                showInGroupFooter: true,
            },{
                column: "product_stock",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",
                showInGroupFooter: true,
            },{
                column: "harga_stok_beli",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",
                showInGroupFooter: true,
            },{
                column: "harga_stok_jual",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",
                showInGroupFooter: true,    
            }],
            totalItems: [{
                column: "product_desc",
                summaryType: "count",
                displayFormat: "Items {0}",
            },{
                column: "harga_stok_beli",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",
            },{
                column: "harga_stok_jual",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",  
            }]
        },
        onExporting(e) {
          const workbook = new ExcelJS.Workbook();
          const worksheet = workbook.addWorksheet('SupplierProducts');
          const storename ="{!!$store->store_name!!}";
          const tglreport ="{!!$tglreport!!}";
          const namafile = "Saldo_Stock_Product_"+tglreport+".xlsx"; 
          DevExpress.excelExporter.exportDataGrid({
            component: e.component,
            worksheet,
            topLeftCell: { row: 6, column: 1 },
          }).then((cellRange) => {
            // header
            const headerRow = worksheet.getRow(2);
            headerRow.height = 30;
            worksheet.mergeCells(2, 1, 2, 8);
            headerRow.getCell(1).value = 'Laporan Saldo Stok Produk';
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
