@extends('layouts.master')
@section('content')
<div class="content">
  <body class="dx-viewport">
      @if(!empty($pesan))
        <div class="alert alert-danger"> {{ $pesan }}</div>
      @endif
      <div class="long-title"><h3>Pembuatan Paket Produk</h3></div>
      <form id="form-container" class="first-group">
          {{-- <div class="card">
              <div class="card-body"> --}}
                <div id="form"></div>
              {{-- </div>
          </div> --}}
          <div id="btnAddProduct"></div>
          <div class="box-body" style="padding-top: 5px;">
              <div id="dataGrid"></div>
            </div>
            <div class="box-body" style="padding-top: 5px;">
              <div id="btnSave"></div>
            </div>
    </form>

{{-- add products dialog --}}
<div class="modal fade" id="mdlAddProduct" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary">
            <h5 class="modal-title"><span class="badge badge-primary">Tambahkan Produk Kedalam Tabel</span></h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-horizontal">
            <div class="row">
              <div class="col-sm-6">
                <div class="panel panel-info">
                    <div class="panel panel-info">
                        <div id="gridProduct"></div>
                    </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="panel panel-info">
                  <div class="panel-body">
                    <div class="form-group row">
                      <label for="txtProdId" class="col-sm-4 control-label text-md-right">ID Produk</label>
                      <div class="col-sm-8">
                        <input id="txtProdId" type="text" name="{{'product_id'}}"
                                class="form-control" placeholder="Product ID" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="txtProdDesc" class="col-sm-4 control-label text-md-right">Deskripsi</label>
                      <div class="col-sm-8">
                        <input id="txtProdDesc" type="text" name="{{'product_shortdesc'}}"
                                class="form-control" placeholder="Description" readonly>
                      </div>
                    </div>
                    <div class="form-group row">
                        <label for="numProdStock" class="col-sm-4 control-label text-md-right">Stok</label>
                        <div class="col-sm-8">
                          <input id="numProdStock" type="number" min="0" name="{{'product_stock'}}"
                                class="form-control" placeholder="Quantity" value="0" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="numProdPrice" class="col-sm-4 control-label text-md-right">Harga</label>
                        <div class="col-sm-8">
                          <input id="numProdPrice" type="number" min="0" name="{{'receive_price'}}"
                                class="form-control" value="0" placeholder="Price">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="numProdQty" class="col-sm-4 control-label text-md-right">Qty</label>
                        <div class="col-sm-8">
                          <input id="numProdQty" type="number" min="0" name="{{'receive_qty'}}"
                                class="form-control" value="0" placeholder="Quantity">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="numProdTotal" class="col-sm-4 control-label text-md-right">Total</label>
                        <div class="col-sm-8">
                          <input id="numProdTotal" type="number" name="" value="0"
                                class="form-control" value="0" placeholder="Total" readonly>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
              <p id="wrmsg" style="color:red; font-size:12px;"></p>
          </div>
        </div>
        <div class="modal-footer">
          <button id="btnAdd" type="button" class="btn btn-info" data-dismiss="modal">Tambahkan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
  {{-- end of add Product dialog --}}
    
  </body>
</div>
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

  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "supplier_id",
          load: function() {
              return jsonFile;
          }
      });
  };
  var storeid ="{!!$storeid!!}";
  var prodDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{URL::to('productstores/stid')}}"+"/"+storeid,
          })
      },
  });
  $("#gridProduct").dxDataGrid({
  dataSource: prodDataSource,//prods,
  // keyExpr: "product_id",
  showBorders: true,
  height: 300,
  searchPanel: {
      visible: true,
      width: 367,
      placeholder: "Cari Produk..."
  },
  selection: {
      mode: "single"
  },
  hoverStateEnabled: true,
  paging: {
    enabled: false
  },
  scrolling: {
    mode: "virtual"
  },
  columns: [
    {
        dataField: "product_id",
        caption: "ID Produk",
        visible:false,
      //  value:"[AUTO NUMBER]",
      //  width:150,
      },{
          dataField: "product_barcode",
          caption: "Barcode",
          // width:125,
          visible:false,
      },{
          dataField: "product_desc",
          caption: "Deskripsi",      
      },{
        dataField: "product_stock",
        caption: "Stok",  
        width:50, 
        // visible:false,
      },{
        dataField: "product_price",
        caption: "Harga",   
        visible:false,
      },
  ],
  onSelectionChanged: function (selectedItems) {
      var data = selectedItems.selectedRowsData[0];           
      if(data) {
          $("#txtProdId").val(data.product_id);
          $("#txtProdDesc").val(data.product_desc);
          $("#numProdStock").val(data.product_stock);
          $("#numProdPrice").val(data.product_price);
          $("#numProdTotal").val(enterProdPrice.value*enterProdQty.value);
      }
  }
});

  $("#form").dxForm({
      colCount: 1,
      items:[{
        itemType:"group",
        colCount:2,
        items: [{
          dataField: "promo_no",
          label:{
            text:"Kode Paket ",
          },
          editorOptions: { 
              value : "Penomoran Otomatis",
              disabled: true
          } 
        },{
          dataField: "promo_desc",
          label:{
            text:"Nama Paket ",
          },
        },{
            dataField: "promo_price",
            label:{
              text:"Harga ",
            }, 
            width: 100,
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
          },
        ]
      }, ]
  });

  // table
  var tbMutINProd = [];
  var dataGrid =$("#dataGrid").dxDataGrid({
      dataSource: tbMutINProd,
      keyExpr: "product_id",
      showBorders: true,
      height: 175,
      paging: {
          enabled: false
      },
      scrolling: {
        mode: "virtual"
      },
      editing: {
            allowDeleting: true,
            useIcons: true,
      },
      columns: [
          {
            caption: "PLU",
            dataField: "product_id",
          },{
              dataField: "product_shortdesc",
              caption:"Deskripsi"
          },{
              dataField: "promo_product_price",
              caption: "Harga Produk",
              dataType: "number",
              format: "fixedPoint",
          },{
              dataField: "promo_product_qty",
              caption: "Qty",
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


  //open form
  $("#btnAddProduct").dxButton({
      text: "Tambah Produk",
      icon: "plus",
      onClick: function(e) {
        var form =$('#form-container').serializeObject();
        if(form['promo_desc']==""){
            DevExpress.ui.notify({
                message: "Silakan isi Nama Paket Produk...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
        }
        $('#mdlAddProduct').modal('show');
      }
  });

  // save penerimaan
  $("#btnSave").dxButton({
      text: "Simpan",
      type: "success",
      width: 125,
      onClick: function(e) {
          var data = tbMutINProd;
          var form =$('#form-container').serializeObject();
          var postdata = [];
          if(form['promo_desc']==""){
            DevExpress.ui.notify({
                message: "Silakan isi Nama Paket Produk...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
          }
          if(form['promo_price']=="" || form['promo_price']==0 ){
            DevExpress.ui.notify({
                message: "Silakan isi Harga Paket Produk...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
          }          

          if(tbMutINProd.length==0){
            {
              DevExpress.ui.notify({
                  message: "Silakan isi produk pada tabel...",
                  position: {
                      my: "center top",
                      at: "center top"
                  }
              }, "warning", 3000);
              return false;
            }
          }

          $.ajax({
              type: "POST",
              url: "{{route('product.package.save')}}",
              data: JSON.stringify({ form: form,table:data}),
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
                       message: "Data Promo Berhasil di Simpan",
                       position: {
                           my: "center top",
                           at: "center top"
                       }
                   }, "success", 3000);
                   location.reload();
                }
              }
          });
          $("#btnSave").dxButton("instance").option("disabled",true);
      }
  });
});
</script>


<script type="text/javascript">
  // script for modal


  var enterProdPrice = document.getElementById("numProdPrice");
  var enterProdQty = document.getElementById("numProdQty");

  enterProdPrice.addEventListener("keyup", function(event) {
    event.preventDefault();
    // if (event.keyCode === 13) {
      // alert('You pressed enter! - keypress');
      $("#numProdTotal").val(enterProdPrice.value*enterProdQty.value);
    // }
  });
  enterProdQty.addEventListener("keyup", function(event) {
    event.preventDefault();
    $("#numProdTotal").val(enterProdPrice.value*enterProdQty.value);
  });
  var arrTabProd=[];

  $("#btnAdd").off('click').click(function(clickEvent){
    var prodid=document.getElementById("txtProdId").value;
    var prodprice=document.getElementById("numProdPrice").value; 
    var prodqty=document.getElementById("numProdQty").value;

 
    if(!prodid){
      // alert('Please Choose Product');
       document.getElementById("wrmsg").innerHTML = 'WARNING : Please Choose Product';
       clickEvent.stopPropagation();
        return;
    }
    if(!prodprice || prodprice===0){
      document.getElementById("wrmsg").innerHTML = 'WARNING : Price Cannot zero or empty';
      clickEvent.stopPropagation();
      return;
    }
    if(!prodqty || prodqty===0){
      document.getElementById("wrmsg").innerHTML = 'WARNING : Quantity Cannot zero or empty';
      clickEvent.stopPropagation();
      return;
    }

    var table=$('#dataGrid').dxDataGrid("instance");
    var arrmop={product_id: prodid,
                product_shortdesc: document.getElementById("txtProdDesc").value,
                promo_product_price: prodprice,
                promo_product_qty: prodqty}

    table.getDataSource().store().insert(arrmop);
    table.refresh();

     //mengembalikan nilai dialog
     $("#txtProdId").val("");
     $("#txtProdId").val("");
     $("#txtProdDesc").val("");
     $("#numProdStock").val("");
     $("#numProdQty").val("");
     $("#numProdTotal").val("");
     $("#numProdPromoPrice").val("");
     $("#numProdQty").val("");
     document.getElementById("wrmsg").innerHTML = '';
  });

</script>
@endsection
