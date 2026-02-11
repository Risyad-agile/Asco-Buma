@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form id="form-container" class="first-group">
    <div id="form"></div>
    <div id="gridContainer"></div>
    <input id="txtreceiveno" type="text" name="saleno" class="form-control" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" hidden>
</form>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
      const receives = {!! $receives !!};
      const supplier = {!! $supplier !!};
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
                return $("<div class='long-title'><h3>Daftar Pembelian {!!$store->store_name!!}</h3></div>");
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
    $("#form").dxForm({
      formData: supplier,
      colCount: 1, 
      items: [{
          dataField: "supplier_id",
          label:{
            text:"ID Supplier ",
          },
          editorOptions: { 
              readOnly: true
          }
        },{
          dataField: "supplier_name",
          label:{
            text:"Supplier",
          },
          editorOptions: { 
              readOnly: true
          }
      }]
    });
    $("#gridContainer").dxDataGrid({
        dataSource: receives,
        keyExpr: "receive_no",
        showBorders: true,
        hoverStateEnabled: true,
        selection: {
            mode: "single"
        },
        "export": {
            enabled: true, 
        },
        allowColumnResizing: true,
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true,
        },
        paging: {
            pageSize: 10,
        },
        columnChooser: {
            enabled: true,
        },
        groupPanel: {
            visible: true
        },
        pager: {
            visible: true,
            allowedPageSizes: [5, 10, 'all'],
            showPageSizeSelector: true,
            showInfo: true,
            showNavigationButtons: true,
        },
        columns: [{
            dataField: "receive_no",
            caption: "No Terima",
          },{
            dataField: "receive_date",
            caption: "Tanggal Terima",
            dataType: "date",
            format:'dd-MM-yyyy',
            visible:false,
          },{
            dataField: "receive_docno",
            caption: "No Surat Jalan",
            visible:false,
          },{
            dataField: "receive_docdate",
            caption: "Tanggal Surat Jalan",
            dataType: "date",
            format:'dd-MM-yyyy',
            visible:false,
          },{
            dataField: "prodcat_desc",
            caption: "Kategori",
            visible:false,
          },{
            dataField: "brand_name",
            caption: "Merek",
            visible:false,
          },{
            dataField: "product_desc",
            caption: "Produk",
            visible:true,
          },{
            dataField: "receive_product_price",
            caption: "Harga",
            format: "fixedPoint",
            visible:true,
          },{
            dataField: "receive_product_qty",
            caption: "Qty",
            visible:true,
          },{
            caption: "Total Harga",
            dataType: "number",
            format: "fixedPoint",
            calculateCellValue: function(rowData) {
                var harga=rowData.receive_product_price;
                var jumlah=rowData.receive_product_qty; 
                var total=harga*jumlah;
                return total;
              }
          },
        ],
        summary: {
          groupItems: [{
              column: "product_desc",
              summaryType: "count",
              displayFormat: "Items : {0}",
              showInGroupFooter: true,
          },{
              column: "receive_product_qty",
              summaryType: "sum",
              valueFormat: "number",
              valueFormat: "fixedPoint",
              displayFormat: "Qty : {0}",
              showInGroupFooter: true,
          },{
              column: "receive_product_price",
              summaryType: "sum",
              valueFormat: "number",
              valueFormat: "fixedPoint",
              displayFormat: "Harga : {0}",
              showInGroupFooter: true,
          },{
              column: "Total Harga",
              summaryType: "sum",
              valueFormat: "number",
              valueFormat: "fixedPoint",
              displayFormat: "Sub Total {0}",
              showInGroupFooter: true,    
          }],
          totalItems: [{
              column: "product_desc",
              summaryType: "count",
              displayFormat: "Items {0}",
          },{
              column: "Total Harga",
              summaryType: "sum",
              valueFormat: "number",
              valueFormat: "fixedPoint",
              displayFormat: "Total Rp. {0}",
          },{
              column: "receive_product_qty",
              summaryType: "sum",
              valueFormat: "number",
              valueFormat: "fixedPoint",
              displayFormat: "Qty {0}",    
            },{
              column: "receive_product_price",
              summaryType: "sum",
              valueFormat: "number",
              valueFormat: "fixedPoint",
              displayFormat: "Harga {0}",    
          }]
        },
        onExporting(e) {
          const workbook = new ExcelJS.Workbook();
          const worksheet = workbook.addWorksheet('SupplierProducts');
          const storename ="{!!$store->store_name!!}";
          const tglreport ="{!!$tglreport!!}";
          const namafile = "Supllier_Product_"+tglreport+".xlsx";
          const suppliername= "{!!$supplier->supplier_name!!}";
          DevExpress.excelExporter.exportDataGrid({
            component: e.component,
            worksheet,
            topLeftCell: { row: 7, column: 1 },
          }).then((cellRange) => {
            // header
            const headerRow = worksheet.getRow(2);
            headerRow.height = 30;
            worksheet.mergeCells(2, 1, 2, 8);
            headerRow.getCell(1).value = 'Daftar Supplier dan Produk';
            headerRow.getCell(1).font = { name: 'Segoe UI Light', size: 22 };
            headerRow.getCell(1).alignment = { horizontal: 'center' };

            const storeRow =worksheet.getRow(3);
            storeRow.getCell(1).value = "Store";
            storeRow.getCell(2).value = storename;
            storeRow.font = { name: 'Segoe UI Light', size: 16 };
            storeRow.alignment = { horizontal: 'left' };

            const supplierRow =worksheet.getRow(4);
            supplierRow.getCell(1).value = "Supplier";
            supplierRow.getCell(2).value = suppliername;
            supplierRow.font = { name: 'Segoe UI Light', size: 16 };
            supplierRow.alignment = { horizontal: 'left' };

            const dateRow =worksheet.getRow(5);
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
        onSelectionChanged: function (selectedItems) {
          var data = selectedItems.selectedRowsData[0];
          $("#txtreceiveno").val(data.receive_no); 
          $("#txtstate").val(data.receive_state); 
        },
      });
  });
</script>
@endsection
