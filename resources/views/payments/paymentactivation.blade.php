@extends('layouts.master')
@section('content')
<div id="toolbar"></div> 
<div id="tablePayments"></div>
{{-- <div class="dx-field-label">NOTE : untuk non aktifkan pilih on, kemudian off lalu simpan</div> --}}
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
                    return $("<div class='long-title'><h3>Aktivasi Jenis Pembayaran</h3></div>");
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
  var stores ={!! $stores !!}
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('payments.create')}}"
          })
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('farma/payments/activation/update')}}"+"/"+kunci,
              method: "PUT",
              data: {values,stores}
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
            dataField:"pay_active_state",
            caption:"Aktifkan",
            dataType:"boolean",
            // width:100,
            editorType: "dxSwitch", 
              editorOptions: { 
                switchedOffText:"Tidak",
                switchedOnText:"Ya",
                width:80,
              }, 
        },
      ],
      onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "pay_active_state")  
                e.editorName = "dxSwitch";  
            },
      onRowUpdated:function(e){
        DevExpress.ui.notify("Aktivasi Pembayaran di Toko Berhasil di Perbaharui"); 
        // var btnUpdate=$("#btnUpdate").dxButton("instance").option("disabled",false);
      },
  });
   
});
</script>
@endsection


