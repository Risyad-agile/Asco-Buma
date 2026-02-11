@extends('layouts.master')
@section('content')
<div class="content">
  <body class="dx-viewport">
      @if(!empty($pesan))
        <div class="alert alert-danger"> {{ $pesan }}</div>
      @endif
      <div class="long-title"><h3>Program Promosi Marketing</h3></div>
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
                  <div class="panel-heading">
                    <div class="form-group">
                          {!! Form::text('doc_date',null,['class' => 'form-control',
                          'id'=>'txtProdSearch','placeholder'=>'Cari...']) !!}
                    </div>
                  </div>
                  <div class="panel-body" style="overflow-y: scroll; height:350px;">
                    <div class="btn-group" role="group" aria-label="...">
                      <table id="tableProd" class="table table-bordered table-striped" style="font-size:14px">
                            @foreach ($products as $key => $prod)
                            <tr>
                                <td>
                                  <a href="#" onclick="sendId('{{$prod->product_id}}','{{$prod->product_desc}}'
                                    ,'{{$prod->product_stock}}','{{$prod->product_price}}');"
                                          id="plist{{$prod->product_id}}" 
                                          name="{{$prod->product_id}}">{{$prod->product_id}}</a>
                                </td>
                                <td>{{$prod->product_desc}}</td>
                            </tr>
                            @endforeach
                      </table>
                    </div>
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
                                  class="form-control" placeholder="ID Produk" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="txtProdDesc" class="col-sm-4 control-label text-md-right">Deskripsi</label>
                        <div class="col-sm-8">
                          <input id="txtProdDesc" type="text" name="{{'product_shortdesc'}}"
                                  class="form-control" placeholder="Deskripsi" readonly>
                        </div>
                      </div>
                      <div class="form-group row">
                          <label for="selectPromoProdStat" class="col-sm-4 control-label text-md-right">Jenis</label>
                          <div class="col-sm-8">
                            <select id="selectPromoProdStat" class="form-control" >
                              <option value="Utama">Utama</option>
                              <option value="Hadiah">Hadiah</option>
                              <option value="Discount">Discount</option>
                            </select>
                          </div>
                      </div>
                      <div class="form-group row">
                        <label for="numProdPrice" class="col-sm-4 control-label text-md-right">Harga Promo</label>
                        <div class="col-sm-8">
                          <input id="numProdPrice" type="number" min="0" name="{{'product_price'}}"
                                class="form-control" value="0" placeholder="Price">
                        </div>
                    </div>
                      <div class="form-group row">
                          <label for="numProdQty" class="col-sm-4 control-label text-md-right">Jumlah Promo</label>
                          <div class="col-sm-8">
                            <input id="numProdQty" type="number" min="0" name="{{'product_qty'}}"
                                  class="form-control" value="0" placeholder="Jumlah">
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
            editorType: "dxSelectBox",
            editorOptions: {
                items: [{"promo_type":"1","promo_type_desc":"Discount"}, // untuk list items
                        {"promo_type":"2","promo_type_desc":"Buy X Get Y"}], //bisa juga discount (harga baru)
                displayExpr: "promo_type_desc",
                valueExpr: "promo_type",
             },
            width: 100,
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
          }, ]
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
                // width: "300px",
                height: 75,
                placeholder : "Ketentuan Promo"
              }
          },]
      },
    ]
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
              caption: "Harga Promo",
              dataType: "number",
              format: "fixedPoint",
          },{
              dataField: "promo_product_qty",
              caption: "Jumlah Promo",
              dataType: "number",
              format: "fixedPoint",
          },{
              dataField: "promo_product_stat",
              caption: "Jenis",
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
                message: "Silakan isi Nama Promo...",
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
                  message: "Silakan isi Harga Promo, atau isi 0 untuk Promo Discount...",
                  position: {
                      my: "center top",
                      at: "center top"
                  }
              }, "warning", 3000);
              return false;
            } 
          if(form['promo_type']=="2"){
            if(form['promo_price']==0 ){
              DevExpress.ui.notify({
                  message: "Silakan isi Harga Promo, harga promo Nol hanya untuk jenis Promo Discount...",
                  position: {
                      my: "center top",
                      at: "center top"
                  }
              }, "warning", 3000);
              return false;
            } 
          }
         
          if(form['promo_type']==""){
            DevExpress.ui.notify({
                message: "Pilih Jenis Promo...",
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
          //jika promo dicount hanya bisa berisi 1 jenis produk
          // if(form['promo_type']=="1"){
          //   if(tbMutINProd.length>1){
          //   {
          //     DevExpress.ui.notify({
          //         message: "Promo dicount hanya untuk satu produk...",
          //         position: {
          //             my: "center top",
          //             at: "center top"
          //         }
          //     }, "warning", 3000);
          //     return false;
          //   }
          // }
          // }
          $.ajax({
              type: "POST",
              url: "{{route('promotions.store')}}",
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
  var prodSearch = document.getElementById("txtProdSearch");
  prodSearch.addEventListener("keyup", function(event) {
    event.preventDefault();
    var kriteria=document.getElementById("txtProdSearch").value ;

    // console.log(kriteria);
    if(kriteria==0){
      kriteria="ALL";
    }
    var url="{{URL::to('products/searchproductbykriteria')}}"+"/"+kriteria;
    $.getJSON( url, function( data ) {
      var eTable="<table id='tableProd' class='table table-bordered table-striped' style='font-size:12px'>"
      for(var i=0;i<data.length;i++)
      {
        eTable += "<tr>";
        eTable += "<td><a href='#' onclick='sendId(\""+data[i].product_id+"\",\"";
        eTable += data[i].product_desc+"\",\"";
        eTable += data[i].product_stock+"\",\"";
        eTable += data[i].product_price+"\");'";
        eTable += "id=plist"+data[i].product_id;
        eTable += "name='"+data[i].product_id+"'>";
        eTable += data[i].product_id+"</a>";
        eTable +="</td>";
        eTable += "<td>"+data[i].product_desc+"</td>";
        eTable += "</tr>";
      }
      eTable +="</table>";
      $('#tableProd').html(eTable);
    });
  });

  function sendId(prodid,proddesc,prodstock,prodprice){
      $("#txtProdId").val(prodid);
      $("#txtProdDesc").val(proddesc);
      $("#numProdStock").val(prodstock);
      $("#numProdPrice").val(prodprice);
      $("#numProdQty").val(1);
      $("#numProdTotal").val(enterProdPrice.value*enterProdQty.value);
  }

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
    var jnspromo=document.getElementById("selectPromoProdStat").value;
    var prodqty=document.getElementById("numProdQty").value;

    if(jnspromo=='Hadiah'){
      prodpromoprice=0;
    }
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
                promo_product_qty: prodqty,
                promo_product_stat: jnspromo}

    table.getDataSource().store().insert(arrmop);
    table.refresh();

     //mengembalikan nilai dialog
     $("#txtProdId").val("");
     $("#txtProdId").val("");
     $("#txtProdDesc").val("");
     $("#numProdStock").val("");
     $("numProdPrice").val("0");
     $("#numProdQty").val("");
     $("#numProdTotal").val("");
     $("#numProdPromoPrice").val("");
     $("#numProdQty").val("");
     document.getElementById("wrmsg").innerHTML = '';
  });

</script>
@endsection
