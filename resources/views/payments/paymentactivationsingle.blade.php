@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="tablePayments"></div>
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
                    return $("<div class='long-title'><h3>Aktivasi Jenis Pembayaran <b>{!!$stores[0]['store_name']!!}</b></h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: 'close',
                    hint: 'Tutup',
                    onClick() {
                        window.location = "{{route('home')}}";
                    },
                },
        
        }]
    });

  var storeid ='{!! $stores[0]['id'] !!}';
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
        return $.ajax({
            url: "{{URL::to('farma/payments/activation/load')}}"+"/"+storeid,
          })
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('farma/payments/activation/update/single')}}"+"/"+kunci,
              method: "PUT",
              data: {values,storeid}
          })
      }
  });

  $("#tablePayments").dxDataGrid({
      dataSource: gridDataSource,
    //   keyExpr: "pay_id",
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
            dataField: "id",
            caption: "ID",
            disabled:true,
            // width:100,
        },{
            dataField: "pay_desc",
            caption: "Deskripsi",      
        },{
            dataField:"active_state",
            caption:"Aktifkan",
            dataType:"boolean",
            // width:100,
        },
      ],
      onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "active_state")  
                e.editorName = "dxSwitch";  
            },
      onEditingStart: function(e){
          if (e.column.dataField != "active_state" ) {
             e.cancel = true;
          }
      },
      onRowUpdated:function(e){
        DevExpress.ui.notify("Aktivasi Pembayaran di Toko Berhasil di Perbaharui"); 
        // var btnUpdate=$("#btnUpdate").dxButton("instance").option("disabled",false);
      },
  });
   
});
</script>
@endsection


