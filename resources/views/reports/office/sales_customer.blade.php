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
            return $("<div class='long-title'><h3>Laporan Rekap Konsumen {{$tglreport}}</h3></div>");
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
            var url="{{URL::to('farma/reports/sales/customer/store')}}"+"/"+storeid+"/start/"+tglawal+"/end/"+tglakhir;
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
            export: {
              enabled: true,
              formats: ['pdf'],
            },
            onExporting(e) {
              const doc = new jsPDF();
              DevExpress.pdfExporter.exportDataGrid({
                jsPDFDocument: doc,
                component: e.component,
                indent: 5,
              }).then(() => {
                doc.save('Sales_Member.pdf');
              });
            
            },
            columns: [
                {
                  dataField:"sale_no",
                  caption: "Nomor Sales",
                },{
                  dataField:"sale_date",
                  caption: "Tanggal",  
                },{
                  dataField:"sale_disc",
                  caption: "Disc",
                  format: "fixedPoint",
                  visible:false,
                },{
                  dataField:"sale_tax",
                  caption: "Pajak",
                  format: "fixedPoint",
                  visible:false,
                },{
                  dataField:"sale_service_charge",
                  caption: "Srv Charge",
                  format: "fixedPoint",
                  visible:false,
                },{
                  dataField:"sale_total",
                  caption: "Total",
                  format: "fixedPoint",
                },
            ],
            sortByGroupSummaryInfo: [{
                summaryItem: "count"
            }],
            summary: {
              totalItems: [{
                    column: "sale_no",
                    summaryType: "count",
                    displayFormat: "Konsumen {0}",
                },{
                    column: "Total",
                    summaryType: "sum",
                    valueFormat: "number",
                    valueFormat: "fixedPoint",
                    displayFormat: "Tot. Sales {0}",  
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
