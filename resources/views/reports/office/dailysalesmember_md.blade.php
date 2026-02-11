@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
 <form id="form-container" class="first-group">
      <div class="dx-field">
          <div class="dx-field-label ">Tanggal Transaksi</div>
          <div class="dx-field-value" >
              <div id="tglsales" class="offset-md-4" ></div>
          </div>
      </div>
      <div class="reports">
          <div id="gridProduct"></div>
      </div>
      <input id="hidtglrep" type="hidden" value="{!!$tglreport!!}">
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
            return $("<div class='long-title'><h3>Laporan Penjualan Harian {{$tglreport}}</h3></div>");
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

    var ndtgl=new Number(tgl.substr(0,2));
    var nmtgl=new Number(tgl.substr(3,2))-1;
    var nytgl=new Number(tgl.substr(6,4));


    $("#tglsales").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        applyValueMode: "useButtons",
        width:"50%",
        value: new Date(nytgl,nmtgl,ndtgl),  //value: new Date(2017, 0, 3), tahun, bulan-1, tanggal
        // label:{
        //   text:"Tanggal Penjualan",
        // },
        
        onValueChanged: function(e){
          // alert(e.actionValue);
          var storeid='{!!$stores->store_id!!}';
          var tanggal=e.value;
          var dtgl=tanggal.getDate();
          var mtgl=tanggal.getMonth();
          var ytgl=tanggal.getFullYear();


          if(dtgl<10){
            dtgl="0"+dtgl;
          }
          mtgl=mtgl+1;
          if(mtgl<10){
            mtgl="0"+mtgl;
          }
          var  tgl=dtgl.toString()+mtgl.toString()+ytgl.toString();
          var url="{{URL::to('agile/reports/daily/sales/member')}}"+"/"+storeid+"/"+tgl;
          $.ajax({
              type: "GET",
              url: url,
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              success: function(response){
                window.location = url;
              },
              complete: function(jqXHR) {
                window.location = url;
              }
            });
        }
    });
    window.jsPDF = window.jspdf.jsPDF;
    var dataGrid = $("#gridProduct").dxDataGrid({
            dataSource: sale,
            allowColumnReordering: true,
            showBorders: true,
            // grouping: {
            //     autoExpandAll: true,
            // },
            searchPanel: {
                visible: true
            },
            paging: {
                pageSize: 10
            },
            // groupPanel: {
            //     visible: true
            // },
            // export: {
            //     enabled: true,
            //     formats: ['pdf'],
            //     // fileName: "Sales",
            //     allowExportSelectedData: true
            // },
            export: {
              enabled: true,
              formats: ['pdf'],
              // allowExportSelectedData: true,
            },
            onExporting(e) {
              const doc = new jsPDF();

              DevExpress.pdfExporter.exportDataGrid({
                jsPDFDocument: doc,
                component: e.component,
                indent: 5,
              }).then(() => {
                doc.save('Sales.pdf');
              });
            },
            columns: [
                {
                  dataField:"members.member_name",
                  caption: "Tamu",
                },{
                  dataField:"members.member_id",
                  caption: "Telepon",
                },{
                  dataField:"members.member_card_no",
                  caption: "No Tiket",
                },{
                  dataField:"members.member_desc",
                  caption: "Kamar",
                },{
                  dataField:"sale_total",
                  caption: "Total Sale",
                  dataType: "number",
                  format: "fixedPoint",
                },
            ],
            masterDetail: {
            enabled: true,
            template: function(container, options) {
                var roProductData = options.data;
                $("<div>")
                    .addClass("master-detail-caption")
                    .text("No Sale : "+roProductData.sale_no)
                    .appendTo(container);
                $("<div>")
                    .dxDataGrid({
                        columnAutoWidth: true,
                        showBorders: true,
                        columns: [
                        {
                            dataField: "product_id",
                            caption: "Produk Id",
                        },
                        {
                            dataField: "product_desc",
                            caption: "Deskripsi Produk",
                        },{
                            dataField: "saleproducts.sale_product_price",
                            caption: "Harga",
                            dataType: "number",
                            format: "fixedPoint",
                        }, {
                            dataField: "saleproducts.sale_product_qty",
                            caption: "Jumlah",
                        },{
                          dataField:"saleproducts.sale_product_disc",
                          caption: "Disc",
                          format: "fixedPoint",
                        },{
                            caption: "Total",
                            dataType: "number",
                            format: "fixedPoint",
                            calculateCellValue: function(rowData) {
                                var harga=rowData.saleproducts.sale_product_price;
                                var jumlah=rowData.saleproducts.sale_product_qty;
                                var disc=rowData.saleproducts.sale_product_disc;
                                var total=(harga*jumlah)-disc;
                                return total;
                            }
                        // },{
                        //   caption: "Gross Profit",
                        //   dataType: "number",
                        //   format: "fixedPoint",
                        //   calculateCellValue: function(rowData) {
                        //       var totaljual=rowData.saleproducts.sale_product_price*rowData.saleproducts.sale_product_qty;
                        //       var totalbeli=rowData.saleproducts.sale_buy_price*rowData.saleproducts.sale_product_qty;
                        //       var disc=rowData.saleproducts.sale_product_disc;
                        //       var gprofit=(totaljual-totalbeli)-disc                     
                        //       return gprofit;
                        //     }
                        }],
                        summary: {
                            totalItems: [{
                                column: "product_id",
                                summaryType: "count",
                                displayFormat: "Jumlah Items {0}",
                            },{
                                column: "Total",
                                summaryType: "sum",
                                dataType: "number",
                                valueFormat: "fixedPoint",
                                displayFormat: "Total Transaksi {0}",
                            }]
                        },
                        dataSource: roProductData.products
                    }).appendTo(container);
              }
        },
          summary: {
            
            totalItems: [{
                  column: "sale_no",
                  summaryType: "count",
                  displayFormat: "Items {0}",
              },{
                  column: "sale_total",
                  summaryType: "sum",
                  valueFormat: "number",
                  valueFormat: "fixedPoint",
                  displayFormat: "Tot. Sales {0}",
              // },{
              //     column: "Gross Profit",
              //     summaryType: "sum",
              //     valueFormat: "number",
              //     valueFormat: "fixedPoint",
              //     displayFormat: "Gr. Profit {0}",    
              }]
          },

        }).dxDataGrid("instance");
  });
  </script>
@endsection
