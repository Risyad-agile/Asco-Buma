@extends('layouts.master')
@section('content')
    <div class="content">
        <div class="long-title"><h3>Aktivasi Produk Close Loop e-Money Semua Toko</h3></div>
        <div id="tabProd"></div>
        {{-- <div class="dx-field-label">NOTE : aktifasi produk emoney untuk yang sudah di aktifasi di toko</div> --}}
        @if($message = Session::get('success'))
        <div class="aler aler-success alert-block">
        <strong>{{$message}}</strong>
        </div>
        @endif
    </div>  
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
        return $.ajax({
            url: "{{URL::to('agile/products/pay/activation/load')}}",
          })
      },
      update: function (key, values) {
          var productid= key.product_id;
          return $.ajax({
              url: "{{URL::to('agile/activation/product/pay/update')}}"+"/"+productid,
              method: "PUT",
              data: {values,productid}
          })
      }
  });

  $("#tabProd").dxDataGrid({
      dataSource: gridDataSource,
      keyExpr: "product_id",
      showBorders: true,
      paging: {
          enabled: false
      },
      pager: {
            showPageSizeSelector: true,
            allowedPageSizes: [5, 10, 15],
            showInfo: true
      },
      editing: {
          mode: "batch",
          allowUpdating: true,
          useIcons: true,
        //   selectTextOnEditStart: true,
            // startEditAction: "click"
      },
      paging: {
          pageSize: 10,
      },
      searchPanel: {
          visible: true,
          highlightCaseSensitive: true,
      },

      columns: [
            {
              dataField: "product_id",
              caption: "ID",
              width:150,
            },{
              caption: "Kategori",
              dataField: "productcats.prodcat_desc",     
              width:200,       
            },{
              dataField:"product_desc",
              caption:"Deskripsi",              
            },{
              dataField:"product_state",
              dataType:"boolean",
              caption:"Aktifkan",
              editorType: "dxSwitch", 
              editorOptions: { 
                switchedOffText:"Tidak",
                switchedOnText:"Ya",
                width:80,
              },  
              width:100,
            },
      ],
      onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "product_state")  
                e.editorName = "dxSwitch";  
            },
      onEditingStart: function(e){
          console.log(e);
          if (e.column.dataField != "product_state" ) {
             e.cancel = true;
          }
      },
      onValueChanged: function(data) {
                    // console.log(data.value);
                    
                //   if(data.value=="1"){
                //     disabledCheckbox.option("value",true);
                //   }else{
                //     disabledCheckbox.option("value",false);
                //   } 
                },
      onRowUpdated:function(e){

        var statawal=e.key.product_state;
        var statakhir=e.data.product_state;
        var proddesc=e.key.product_shortdesc;
        var prodstock=e.key.product_qty;
        if(prodstock!=0)
        {
            DevExpress.ui.notify("Produk ".concat(proddesc," gagal di nonaktifkan : masih ada stok"));
            return;
        }

        if(statawal=='0'){  //awalnya belum aktif
            if(statakhir=='1'){ //status akhir aktif 
                DevExpress.ui.notify("Produk ".concat(proddesc," berhasil diaktifkan")); 
            }
        }
        if(statawal=='1'){  //awalnya aktif
            if(statakhir=='0'){ //status akhir tidak aktif
                DevExpress.ui.notify("Produk ".concat(proddesc," berhasil di nonaktifkan")); 
            }
        }       

      },
  });
  
});
</script>
@endsection


