@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="tableProduct"></div>

{{-- add receive desc dialog --}}
<div class="modal fade" id="mdlNextData" role="dialog">
  <div class="modal-dialog modal-lg">
      <div class="modal-content">
          <div class="modal-header bg-primary">
          <h3 id="mdlNextDataTitle" class="modal-title"><span class="badge badge-primary">Informasi Promo</span></h3>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="form-container" class="first-group">
            <div id="form"></div>
            <div id="btnSave"></div>
        </form>
      </div>
  </div>
</div>
</div>
{{-- end receive desc dialog --}}


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

  var products = {!! $products !!}; 

  // table
  var dataGrid =$("#tableProduct").dxDataGrid({
      dataSource: products,
      keyExpr: "product_id",
      showBorders: true,
      height: 450,
      searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
      paging: {
          enabled: false
      },
      scrolling: {
        mode: "virtual"
      },
      editing: {
            mode: "batch",
            allowDeleting: true,
            allowUpdating:true,
            useIcons: true,
      },
      columns: [
          {
            caption: "ID Produk",
            dataField: "product_id",
            visible:false,
          },{
            dataField: "productcats.prodcat_desc",
            caption:"Kategori",
          },{
            dataField: "product_desc",
            caption:"Deskripsi"
          },{
            dataField: "product_price",
            caption: "Harga Jual Rata-Rata",
            dataType: "number",
            format: "fixedPoint",
           },{
            dataField: "product_state",
            caption: "Produk Utama",
            dataType:"boolean",
             editorType:"dxSwitch",
            editorOptions: { 
                switchedOffText:"Bukan",
                switchedOnText:"Ya",
                width:80,
            }, 
          },{
            caption: "Jumlah",
            dataField: "product_qty",
            dataType: "number",
            format: "fixedPoint",
           },
      ],
      toolbar: {       
            items: ['searchPanel','saveButton','revertButton',{
              location: 'center',
              locateInMenu: 'never',
              template: function() {
                  return $("<div class='toolbar-label'><b>(Buy X Get Y, Buy X+Y+Z with Special Price)</b></div>");
              }
            }
      ]},
      onEditorPreparing: function (e) {  
        if (e.parentType == "dataRow" && e.dataField == "product_state")  
            e.editorName = "dxSwitch";  
        },
      onEditingStart: function(e){
        if (e.column.dataField != "product_qty" && e.column.dataField != "product_state") {
            e.cancel = true;
        }
      },
      summary: {
      totalItems: [{
          column: "product_desc",
          summaryType: "count",
          displayFormat: "Jumlah Data: {0}",
      },{
          column: "product_qty",
          summaryType: "sum",
          dataType:"number",
          valueFormat: "fixedPoint",
          displayFormat: "Total: {0}",

      }]
  }
}).dxDataGrid("instance");
  var weekdays = [{ id: 1, text: "Senin"},
              { id: 2, text: "Selasa"},
              { id: 3, text: "Rabu"},
              { id: 4, text: "Kamis"},
              { id: 5, text: "Jumat"},
              { id: 6, text: "Sabtu"}, 
              { id: 7, text: "Minggu"}];
  var selecteddays=[];
  //open form
  $("#form").dxForm({
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
              value : "Penomoran Otomatis",
              disabled: true
          }
        },{
          dataField: "promo_date_start",
          label:{
            text:"Tanggal Mulai Promo",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              value : new Date(),
          }
        },{
          dataField: "promo_desc",
          label:{
            text:"Nama Promo ",
          },
          editorOptions: {
          }
        },{
          dataField: "promo_date_end",
          label:{
            text:"Tanggal Akhir Promo ",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              value : new Date(),
          }
        },{
            dataField: "promo_type",
            label:{
              text:"Jenis Promosi",
            },  
            editorOptions: {
                value : "Merchant",
                disabled: true,
             },
            width: 100,
        },{
            dataField: "promo_price",
            label:{
              text:"Harga ",
            }, 
            width: 100,
            editorType: "dxNumberBox",
            editorOptions: {
                dataType:"number",
                format: "#,##0",
             },
            validationRules: [{
                type: "required",
                message: "Jumlah harus di isi...",
            },{
                type: "range",
                min:0,
                message: "Jumlah harus positif",
            },]
        },]
      },{
        itemType:"group",
        colCount:1,
        items:[
          {
            dataField: "promo_days",
            label:{
              text:"Pilih Hari",
            },
            editorType: "dxList",
            editorOptions: {
              items:weekdays,
              selectionMode: "all",
              height: 200,
              showSelectionControls: true,
              onContentReady: function(e) {
                e.component.selectAll();
              },
              onSelectionChanged: function(data) {
                selecteddays=data.component._options.selectedItemKeys;
              }
            }
        },]
      },{
        itemType:"group",
        colCount:1,
        items:[
          {
            dataField: "promo_rule",
            label:{
              text:"Ketentuan",
            },
            editorType: "dxTextArea",
            editorOptions: {
                maxLength:100,// width: "300px",
                height: 75,
                placeholder : "Keterangan"
              }
          },]
      },]
  }); 

 
  // save penerimaan
  $("#btnSave").dxButton({
      text: "Simpan",
      type: "success",
      width: 125,
      onClick: function(e) {
          var data = products;
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
          if(form['promo_price']=="" || form['promo_price']==0 ){
              DevExpress.ui.notify({
                  message: "Silakan isi Harga Promo...",
                  position: {
                      my: "center top",
                      at: "center top"
                  }
              }, "warning", 3000);
              return false;
          } 
          $.ajax({
              type: "POST",
              url: "{{route('promotions.merchant.save')}}",
              data: JSON.stringify({form:form,table:data,selecteddays:selecteddays}),
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: "Validation Error",
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else{
                    swal({
                        title: "OK",
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    })
                    .then((value) => {
                          window.location = '{{route('promotions.merchant.index')}}';
                    });
                }
                $("#tableProduct").dxDataGrid("instance").refresh();
                return false;
              }, 
              error: function(jqXHR, textStatus, errorThrown) {
                swal({
                    title: "Validation Error",
                    icon: data.status,
                    text: data.message,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                return false;
              } 
          });
          return false; 
      }
  });
  $("#toolbar").dxToolbar({
    items: [{
              location: 'center',
              locateInMenu: 'never',
              template: function() {
                return $("<div class='long-title'><h3>Buat Promo Merchandise Baru</h3></div>");
              }
          },{
          location: 'after',
          widget: 'dxButton',
          locateInMenu: 'auto',
            options: {
                icon: "arrowright",
                hint: 'Proses Selanjutnya',
                onClick: function() {
                    
                    var adaUtama=0;
                    for (i = 0; i < products.length; i++) {
                        if(products[i]['product_state']==true){
                          adaUtama++;
                        }
                    }
                    // console.log(adaUtama);
                    
                    if(adaUtama!=1){
                      DevExpress.ui.notify({
                            message: "Silakan Aktifkan HANYA Satu Produk sebagai Produk Utama...",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        return false;
                    }
                    var table=$('#tableProduct').dxDataGrid("instance");
                    var formWidget = $("#form").dxForm("instance");  
                    var totalqty=table.getTotalSummaryValue('product_qty');
                    if(totalqty==0){
                        DevExpress.ui.notify({
                            message: "Tidak ada produk Merchant untuk di proses...",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        return false;
                    }
                    $('#mdlNextData').modal('show');
                }
            }
          },{
              location: 'after',
              widget: 'dxButton',
              locateInMenu: 'auto',
              options: {
                  icon: 'close',
                  hint: 'Tutup',
                  onClick() {
                      window.location = "{{route('promotions.merchant.index')}}";
                  },
              },
        }]
    });
});
</script>
@endsection
