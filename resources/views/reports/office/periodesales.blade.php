@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div class="short-title"><h4>{!!$store->store_name!!}</h4></div>
<div class="short-title"><h4>Periode {{$tglreport}}</h4></div>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="dx-field-label">Tanggal Mulai</div>
        <div class="dx-field-value">
            <div id="tglawal"></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="dx-field-label">Tanggal Akhir</div>
        <div class="dx-field-value">
            <div id="tglakhir"></div>
        </div>
    </div>  
</div>
<div class="content justify-content-center">
    <div id="btnProses"></div> 
</div>
<input id="hidtglrep" type="hidden" value="{!!$tglreport!!}">
<input id="hidstoreid" type="hidden" value="{!!$store->id!!}">
<div id="gridProduct"></div>
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
            return $("<div class='long-title'><h3>Laporan Penjualan Periodik</h3></div>");
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
    var sale = {!! $sales !!};
    var tgl =document.getElementById("hidtglrep").value;
    var storeid=document.getElementById("hidstoreid").value;
    var tanggalfile=tgl;

    var ndtgl=new Number(tgl.substr(0,2));
    var nmtgl=new Number(tgl.substr(3,2))-1;
    var nytgl=new Number(tgl.substr(6,4));

    const tglawal='{!!$tglawal!!}';
    const tglakhir='{!!$tglakhir!!}';
    $("#tglawal").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        type: 'date',
        value: new Date(tglawal),
    });
    $("#tglakhir").dxDateBox({
        displayFormat: "dd-MM-yyyy", 
        type: 'date',
        value: new Date(tglakhir),
    });
    $("#btnProses").dxButton({
        type: "success",
        text: "Proses Periode Laporan",
        useSubmitBehavior: true,
        onClick: function(e) {      
            var tglawal=getSelectedDate($('#tglawal').dxDateBox("instance").option("value"));
            var tglakhir=getSelectedDate($('#tglakhir').dxDateBox("instance").option("value"));
            tanggalfile=tglawal+"_"+tglakhir;
            var url="{{URL::to('farma/reports/sales/periode/store')}}"+"/"+storeid+"/start/"+tglawal+"/end/"+tglakhir;
            $.ajax({
                type: "GET",
                url: url,
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function(response){
                    window.location = url;
                },
                complete: function(response) {
                    window.location = url;
                }
            });
        }
    });
    var dataGrid = $("#gridProduct").dxDataGrid({
            dataSource: sale,
            allowColumnReordering: true,
            allowColumnResizing: true,
            showBorders: true,
            grouping: {
                autoExpandAll: true,
            },
            searchPanel: {
                visible: true
            },
            columnChooser: {
                enabled: true,
            },
            paging: {
                pageSize: 10
            },
            groupPanel: {
                visible: true
            },
            "export": {
                enabled: true, 
            },
            onExporting(e) {
              const workbook = new ExcelJS.Workbook();
              const worksheet = workbook.addWorksheet('DailySales');
              const storename ="{!!$store->store_name!!}";
              const tglreport ="{!!$tglreport!!}";
              const namafile = "PeriodeSales_"+tanggalfile+".xlsx";

              DevExpress.excelExporter.exportDataGrid({
                component: e.component,
                worksheet,
                topLeftCell: { row: 6, column: 1 },
              }).then((cellRange) => {
                // header
                const headerRow = worksheet.getRow(2);
                headerRow.height = 30;
                worksheet.mergeCells(2, 1, 2, 8);
                headerRow.getCell(1).value = 'Laporan Penjualan Periodik';
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
                  dataField:"product_plu",
                  caption: "PLU",
                  visible:false,
                //   width:100,
                },{
                  dataField:"sale_date",
                  caption: "Tanggal",
                  dataType: "date",
                  format: "dd-MM-yyyy",
                //   width:100,
                },{
                  caption: "Nomor Transaksi",
                  dataField: "sale_no",
                },{
                  dataField:"prodcat_desc",
                  caption: "Kategori", 
                  visible:false,
                },{
                  dataField:"product_name",
                  caption: "Produk",
                },{
                  dataField:"sale_product_qty",
                  caption: "Jumlah",
                //   width:70,
                },{
                  dataField:"sale_product_buy_price",
                  caption: "Harga Beli",
                  format: "fixedPoint",
                },{
                  dataField:"sale_product_price",
                  caption: "Harga",
                  format: "fixedPoint",
                //   width:100,
                },{
                  dataField:"sale_product_disc",
                  caption: "Disc",
                  format: "fixedPoint",
                  visible:false,
                //   width:100,
                },{
                  dataField:"sale_product_total",
                  caption: "Total Jual",
                  format: "fixedPoint",
                  visible:false,
                //   width:100,  
                },{
                  caption: "Total",
                  dataType: "number",
                  format: "fixedPoint",
                //   width:150,
                  calculateCellValue: function(rowData) {
                      var harga=rowData.sale_product_price;
                      var jumlah=rowData.sale_product_qty;
                      var disc=rowData.sale_product_disc;
                      var total=(harga*jumlah)-disc;
                      return total;
                    }
                },{
                  caption: "Margin",
                  dataType: "number",
                  format: "fixedPoint", 
                  calculateCellValue: function(rowData) {
                      var totaljual=rowData.sale_product_price*rowData.sale_product_qty;
                      var totalbeli=rowData.sale_product_buy_price*rowData.sale_product_qty;
                      var disc=rowData.sale_product_disc;
                      var gprofit=(totaljual-disc)-totalbeli;                      
                      return gprofit;
                    }
                },{
                  dataField:"sale_tax",
                  caption: "Tax",
                  format: "fixedPoint",
                  visible:false,
                  // width:100,
                },{
                  dataField:"sale_service_charge",
                  caption: "Service",
                  format: "fixedPoint",
                  visible:false,
                  // width:100,
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
  function getSelectedDate(selectedDate){
    var dtgl=selectedDate.getDate();
    var mtgl=selectedDate.getMonth();
    var ytgl=selectedDate.getFullYear();
    if(dtgl<10){
        dtgl="0"+dtgl;
    }
    mtgl=mtgl+1;
    if(mtgl<10){
        mtgl="0"+mtgl;
    }
    var tgl=ytgl.toString()+"-"+mtgl.toString()+"-"+dtgl.toString();
    return tgl;
  }
</script>
@endsection
