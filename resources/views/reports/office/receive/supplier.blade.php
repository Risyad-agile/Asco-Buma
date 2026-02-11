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
<input id="hidstoreid" type="hidden" value="{!!$store->store_id!!}">
<div id="gridContainer"></div>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
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
            return $("<div class='long-title'><h3>Daftar Pembelian</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
                onClick: function(e) {      
                    window.location = "{{route('home')}}";
                }
            }
        }]
    });
    var tgl =document.getElementById("hidtglrep").value;
    var storeid=document.getElementById("hidstoreid").value;
    var tanggalfile=tgl;

    var ndtgl=new Number(tgl.substr(0,2));
    var nmtgl=new Number(tgl.substr(3,2))-1;
    var nytgl=new Number(tgl.substr(6,4));
    $("#tglawal").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        value: new Date(),
    });
    $("#tglakhir").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        value: new Date(),
    });
    $("#btnProses").dxButton({
        type: "success",
        text: "Proses Periode Laporan",
        useSubmitBehavior: true,
        onClick: function(e) {      
            var datebox_awal=$('#tglawal').dxDateBox("instance").option("value");
            var dtglawal=datebox_awal.getDate();
            var mtglawal=datebox_awal.getMonth();
            var ytglawal=datebox_awal.getFullYear();
            if(dtglawal<10){
                dtglawal="0"+dtglawal;
            }
            mtglawal=mtglawal+1;
            if(mtglawal<10){
                mtglawal="0"+mtglawal;
            }
            var tglawal=ytglawal.toString()+"-"+mtglawal.toString()+"-"+dtglawal.toString();
            var datebox_akhir=$('#tglakhir').dxDateBox("instance").option("value");
            var dtglakhir=datebox_akhir.getDate();
            var mtglakhir=datebox_akhir.getMonth();
            var ytglakhir=datebox_akhir.getFullYear();
            if(dtglakhir<10){
                dtglakhir="0"+dtglakhir;
            }
            mtglakhir=mtglakhir+1;
            if(mtglakhir<10){
                mtglakhir="0"+mtglakhir;
            }
            var  tglakhir=ytglakhir.toString()+"-"+mtglakhir.toString()+"-"+dtglakhir.toString();
            tanggalfile=tglawal+"_"+tglakhir;
            var url="{{URL::to('agile/reports/purchase/supplier/periode/open')}}"+"/"+storeid+"/"+tglawal+"/"+tglakhir;
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

      $("#gridContainer").dxDataGrid({
            dataSource: {!! $receives !!},
            keyExpr: "receive_no",
            showBorders: true,
            hoverStateEnabled: true,
            "export": {
                  enabled: true,
                  fileName: "purchase_supplier",
              },
            allowColumnResizing: true,
            columnChooser: {
                enabled: true
            },
            searchPanel: {
                visible: true,
                highlightCaseSensitive: true,
            },
            paging: {
                pageSize: 10,
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
                    dataField: "receive_date",
                    caption: "Tanggal",
                    dataType: "date",
                    format:'dd-MM-yyyy',
                },{
                    dataField: "receive_docno",
                    caption: "No Surat Jalan",
                },{
                    dataField: "receive_no",
                    caption: "No Terima",
                    visible:false,
                },{
                    dataField: "suppliers.supplier_name",
                    caption: "Supplier",
                },{
                    dataField: "receive_docdate",
                    caption: "Tanggal Surat Jalan",
                    dataType: "date",
                    format:'dd-MM-yyyy',
                    visible:false,
                },{
                    dataField: "receive_total",
                    caption: "Nominal",
                    dataType:"number",
                    format: "fixedPoint",
                },{
                    dataField: "receive_note",
                    caption: "Keterangan",
                    visible:true,
              },
          ],
          sortByGroupSummaryInfo: [{
            summaryItem: "count"
        }],
        summary: {
            groupItems: [{
                column: "receive_total",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}",
                showInGroupFooter: true,
            }],
            totalItems: [{
                column: "receive_total",
                summaryType: "sum",
                valueFormat: "number",
                valueFormat: "fixedPoint",
                displayFormat: "Total : {0}", 
            }]
        }
      });
  });
</script>
@endsection
