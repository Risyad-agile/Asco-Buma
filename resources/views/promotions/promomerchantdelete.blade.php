@extends('layouts.master')
@section('content')
<div id="toolbar"></div> 
<form id="form-container" class="first-group">
      <div id="form"></div>
      <div class="box-body" style="padding-top: 5px;">
        <div id="dataGrid"></div>
      </div>
      <div class="box-body" style="padding-top: 5px;">
        <div id="btnSave"></div>
      </div>
</form>
@endsection

@section('script')
<script type="text/javascript">
$(function() {
  $.fn.serializeObject = function(){
      var o = {};
      var a = this.serializeArray();
      $.each(a, function() {
          if (o[this.name] !== undefined) {
              if (!o[this.name].push) {
                  o[this.name] = [o[this.name]];
              }
              o[this.name].push(this.value || '');
          } else {
              o[this.name] = this.value || '';
          }
      });
      return o;
  };

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
            return $("<div class='long-title'><h3>Delete Promo Merchant</h3></div>");
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
                    window.location = "{{route('promotions.merchant.index')}}";
                }
            }
        }]
    });  
  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "promo_no",
          load: function() {
              return jsonFile;
          }
      });
  };
  var promo={!!$promo!!}; 
  $("#form").dxForm({
      formData: promo,
      colCount: 1,
      items:[
      {
        itemType:"group",
        colCount:2,
        items: [{
          dataField: "promo_no",
          label:{
            text:"Kode Promo ",
          },
          editorOptions: { 
              readOnly: true,
          }
        },{
          dataField: "promo_date_start",
          label:{
            text:"Tanggal Mulai Promo",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              readOnly: true,
          }
        },{
          dataField: "promo_desc",
          label:{
            text:"Nama Promo ",
          },
          editorOptions: {
            readOnly: true,
          }
        },{
          dataField: "promo_date_end",
          label:{
            text:"Tanggal Akhir Promo ",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              readOnly: true,
          }
        },]
      },{
        itemType:"group",
        colCount:2,
        items:[
          {
            dataField: "promo_type",
            label:{
              text:"Jenis Promosi",
            },  
            editorOptions: {
                value : "Merchant",
                readOnly: true,
             },
            // width: 100,
          },{
            dataField: "promo_price",
            label:{
              text:"Harga ",
            }, 
            dataType:"number",
            format: "fixedPoint",
            validationRules: [{
                type: "required",
                message: "Jumlah harus di isi...",
            },{
                type: "range",
                min:0,
                message: "Jumlah harus positif",
            },]    
          }]
      },
      {
        itemType:"group",
        colCount:2,
        items:[
          {
            dataField: "promo_rule",
            label:{
              text:"Ketentuan ",
            },
            editorType: "dxTextArea",
            editorOptions: {
                readOnly: true,
                height: 75,
                placeholder : "Ketentuan Promo"
              }
          },]
      },
    ]
  });

  // table
  var products = {!!$promo->products!!};
 
  var dataGrid =$("#dataGrid").dxDataGrid({
      dataSource: products,
      keyExpr: "product_id",
      showBorders: true,
      height: 300,
      paging: {
          enabled: false
      },
      scrolling: {
        mode: "virtual"
      },
      columns: [
          {
            caption: "PLU",
            dataField: "product_id",
          },{
              dataField: "product_shortdesc",
              caption:"Deskripsi"
          },{
              dataField: "product_price",
              caption: "Harga Jual Rata-rata",
              dataType: "number",
              format: "fixedPoint",
          },{
              dataField: "promotionproducts.promo_product_qty",
              caption: "Jumlah",
              dataType: "number",
              format: "fixedPoint",
          },
      ],
      summary: {
      totalItems: [{
          column: "product_id",
          summaryType: "count",
          displayFormat: "Data: {0}",
      },
    ]
  }
  }).dxDataGrid("instance");


  // save penerimaan
  $("#btnSave").dxButton({
      text: "Hapus",
      type: "danger",
      width: 125,
      onClick: function(e) {
          var datatable = products;
          var form =$('#form-container').serializeObject();
          if(form['promo_desc']==""){
            DevExpress.ui.notify({
                message: "Silakan isi Nama Promo...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
          }
          var promono=form['promo_no'];
          var result = DevExpress.ui.dialog.confirm("Promosi dengan nomor "+promono+" dihapus, Lanjutkan...??", "Konfirmasi");
            result.done(function (dialogResult) {
                if(dialogResult){
                    $.ajax({
                        type: "DELETE",
                        url: "{{URL::to('office/promotions')}}"+"/"+promono,
                        data: JSON.stringify({form:form,table:datatable}),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        beforeSend: function()
                        {
                            //do before send
                        },
                        success: function(response){
                        },
                        failure: function(errMsg) {
                            alert(errMsg);
                        },
                        complete: function(jqXHR) {
                        if(jqXHR.readyState === 4) {
                            DevExpress.ui.notify({
                                message: "Data Promo Berhasil di Hapus",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "success", 3000);
                               window.location = "{{route('promotions.merchant.index')}}";
                            }
                        }
                    });
                }
            });
      }
  });
});
</script>


@endsection
