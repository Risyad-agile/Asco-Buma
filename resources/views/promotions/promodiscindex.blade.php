@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form method="POST" action="{{route('promotions.discount.main')}}">
    @csrf
    <div class="second-group">
        <div id="tablePromo"></div>
    </div>
    <input id="txtPromoNo" type="text" name="promo_no" class="form-control" hidden >
    <input id="txtPromoState" type="text" name="promo_state" class="form-control" hidden>
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
            return $("<div class='long-title'><h3>Daftar Promo Discount</h3></div>");
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
  var promos = {!! $promos !!};
  
  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "supplier_id",
          load: function() {
              return jsonFile;
          }
      });
  };

  // table

  var dataGrid =$("#tablePromo").dxDataGrid({
          dataSource: promos,
          keyExpr: "promo_no",
          showBorders: true,
          allowColumnResizing: true,
          selection: {
            mode: "single"
          },
          hoverStateEnabled: true,
          searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
          paging: {
              pageSize: 10,
          },
          columns: [{
                  dataField: "promo_no",
                  caption: "Kode Promo",
              },{
                  dataField: "promo_desc",
                  caption: "Nama Promo",
              },{
                  dataField: "promo_date_start",
                  caption: "Tanggal Mulai",
                  dataType: "date",
                  format:'dd-MM-yyyy',
              },{
                  dataField: "promo_date_end",
                  caption: "Tanggal Akhir",
                  dataType: "date",
                  format:'dd-MM-yyyy',
              },{
                  dataField: "promo_type",
                  caption: "Tipe Promo",
                  calculateCellValue: function(rowData) {
                    var status="Free Items";
                    if(rowData.promo_type!='2'){
                      status="Discount";
                    }
                    return status;
                  }
              },{
                  dataField: "promo_state",
                  caption: "Status Aktif",
                  calculateCellValue: function(rowData) {
                    var status="Aktif";
                    if(rowData.promo_state=='0'){
                      status="Tidak Aktif";
                    }
                    return status;
                  }
              },
          ],      
          toolbar: {       
            items: ['searchPanel','saveButton','revertButton',{
              location: 'center',
              locateInMenu: 'never',
              template: function() {
                  return $("<div class='toolbar-label'><b>Harga Setelah Discount Tergantung Harga Dasar Setiap Toko</b></div>");
              }
            },{
              location: 'after',
              widget: 'dxButton',
              locateInMenu: 'auto',
              options: {
                  icon: "plus",
                  hint: 'Buat Promo Discount Baru',
                  useSubmitBehavior: true,
                  onClick: function(e) {      
                    var txtPromoNo=document.getElementById("txtPromoNo").value;
                    if(txtPromoNo!=""){
                        $("#txtPromoNo").val(""); //supaya ke server jadi null
                    }
                 }
              }
            },{
              location: 'after',
              widget: 'dxButton',
              locateInMenu: 'auto',
              options: {
                  icon: "edit",
                  hint: 'Update Promo Discount',
                  useSubmitBehavior: true,
                  onClick: function(e) {      
                    var txtPromoNo=document.getElementById("txtPromoNo").value;
                    if(txtPromoNo==""){
                        DevExpress.ui.notify({
                            message: "Silakan Pilih Nomor Promo..",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        e.preventDefault();
                        return false;
                    }
                    $("#txtPromoState").val("UPDATE"); //kirim perintah update ke server
                  }
                }
            },{
              location: 'after',
              widget: 'dxButton',
              locateInMenu: 'auto',
              options: {
                  icon: "trash",
                  hint: 'Hapus Promo Discount',
                  useSubmitBehavior: true,
                  onClick: function(e) {      
                    var txtPromoNo=document.getElementById("txtPromoNo").value;
                    var txtPromoState=document.getElementById("txtPromoState").value;
                    if(txtPromoNo==""){
                        DevExpress.ui.notify({
                            message: "Silakan Pilih Nomor Promo..",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        e.preventDefault();
                        return false;
                    }
                    if(txtPromoState!="0"){
                        DevExpress.ui.notify({
                            message: "Proses Hapus hanya untuk promo yang tidak aktif",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "error", 3000);
                        e.preventDefault();
                        return false;
                    }
                    $("#txtPromoState").val("DELETE"); //kirim perintah hapus ke server
                  }
                }
            }]},  
          onSelectionChanged: function (selectedItems) {
            var data = selectedItems.selectedRowsData[0];
            $("#txtPromoNo").val(data.promo_no);
            $("#txtPromoState").val(data.promo_state);
          },
          masterDetail: {
            enabled: true,
            template: function(container, options) {
                var roProductData = options.data;
                $("<div>")
                    .addClass("master-detail-caption")
                    .text("Ketentuan Promo : "+roProductData.promo_rule )
                    .appendTo(container);
                $("<div>")
                    .dxDataGrid({
                        columnAutoWidth: true,
                        showBorders: true,
                        columns: [
                        {
                            dataField: "product_id",
                            caption: "PLU",
                        },{
                            dataField: "product_desc",
                            caption: "Deskripsi",
                        },{
                            dataField: "product_price",
                            caption: "Harga Jual Rata-Rata",
                            dataType: "number",
                            format: "fixedPoint",
                        },{
                            dataField: "promotionproducts.promo_product_qty",
                            caption: "Jumlah Promo",
                        },{
                            dataField: "promotionproducts.promo_product_disc",
                            caption: "Discount",
                            dataType: "number",
                            format: "fixedPoint",
                        }],
                        summary: {
                            totalItems: [{
                                column: "product_id",
                                summaryType: "count",
                                displayFormat: "Jumlah Products {0}",
                            }]
                        },
                        dataSource: roProductData.products
                    }).appendTo(container);
              }
          }
      });
   
});
</script>
@endsection
