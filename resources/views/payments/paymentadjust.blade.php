@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="tablePayments"></div>
{{-- <div class="dx-field-label">NOTE : fasilitas ini untuk mengubah nilai Pembayaran </div> --}}
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
            return $("<div class='long-title'><h3>Penyesuaian Pembayaran <b>{!! $stores[0]['store_name'] !!}</h3></div>");
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
  var stores ={!! $stores !!}
  var storeid ='{!! $stores[0]['store_id'] !!}';
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
            url: "{{URL::to('agile/payments/adjustment/load')}}"+"/"+storeid,
          })
      },
      update: function (key, values) {
          var kunci= key.pay_id;
          return $.ajax({
              url: "{{URL::to('agile/payments/adjustment/update')}}"+"/"+kunci,
              method: "PUT",
              data: {values,stores}
          })
      }
  });

  $("#tablePayments").dxDataGrid({
      dataSource: gridDataSource,
      keyExpr: "pay_id",
      showBorders: true,
      paging: {
          enabled: false
      },
      editing: {
          mode: "batch",
          allowUpdating: true,
          useIcons: true,
      },
      paging: {
          pageSize: 10
      },
      searchPanel: {
          visible: true,
          highlightCaseSensitive: true,
      },
      columns: [
        {
            dataField: "pay_id",
            caption: "Kode",
            disabled:true,
            // width:100,
        },{
            dataField: "pay_desc",
            caption: "Deskripsi",  
        },{
            dataField: "stores.0.paymentstores.adjust_state",
            caption: "Status",   
            lookup: {
                    dataSource: [{"adjust_state":"0","adjust_state_desc":"Default"},
                            {"adjust_state":"1","adjust_state_desc":"Plus"},
                            {"adjust_state":"2","adjust_state_desc":"Minus"}],
                    valueExpr: "adjust_state",
                    displayExpr: "adjust_state_desc",
                },     
        },{
            dataField:"stores.0.paymentstores.adjust_value",
            caption:"Nilai",
            dataType:"number",
            format: "percent",
            validationRules: [{
                min:0,
                max:1, 
                type: "range",
                message: "Nilai antara 0 dan 1, 1=100%"
            }] 
        },
      ],
    //   toolbar: {
    //     items: [
    //         'searchPanel','saveButton','revertButton',
    //         {
    //             location: 'after',
    //             widget: 'dxButton',
    //             options: {
    //                 icon: 'close',
    //                 hint: 'Tutup',
    //                 onClick() {
    //                     window.location = "{{route('home')}}";
    //                 },
    //             },
    //         },
    //     ],},      
      onRowUpdated:function(e){
        DevExpress.ui.notify("Aktivasi Pembayaran di Toko Berhasil di Perbaharui"); 
        // var btnUpdate=$("#btnUpdate").dxButton("instance").option("disabled",false);
      },
  });
   
});
</script>
@endsection


