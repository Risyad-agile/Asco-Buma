@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form id="form-container" class="first-group">
    <div id="form"></div>
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
                          {{-- {!! Form::text('doc_date',null,['class' => 'form-control',
                          'id'=>'txtProdSearch','placeholder'=>'Cari...']) !!} --}}
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
                        <label for="txtProdPrice" class="col-sm-4 control-label text-md-right">Harga</label>
                        <div class="col-sm-8">
                          <input id="txtProdPrice" type="text" name="{{'product_shortdesc'}}"
                                  class="form-control" placeholder="Harga Rata Rata" readonly>
                        </div>
                      </div>                        
                      <div class="form-group row">
                        <label for="numProdDisc" class="col-sm-4 control-label text-md-right">Discount</label>
                        <div class="col-sm-8">
                          <input id="numProdDisc" type="number" min="0" name="{{'product_disc'}}"
                                class="form-control" value="0" placeholder="Price">
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
            return $("<div class='long-title'><h3>Pembaharuan Promo Discount</h3></div>");
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
                    window.location = "{{route('promotions.discount.index')}}";
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
              readOnly: true
          }
        },{
          dataField: "promo_date_start",
          label:{
            text:"Tanggal Mulai Promo",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
            //   value : new Date(),
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
            //   value : new Date(),
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
                value : "Discount",
                readOnly: true,
             },
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
                // width: "300px",
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
      height: 250,
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
              dataField: "product_price",
              caption: "Harga Jual Rata-rata",
              dataType: "number",
              format: "fixedPoint",
          },{
              dataField: "promotionproducts.promo_product_qty",
              caption: "Jumlah",
              dataType: "number",
              format: "fixedPoint",
          },{
              dataField: "promotionproducts.promo_product_disc",
              caption: "Discount",
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


          if(products.length==0){
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
              url: "{{route('promotions.discount.update')}}",
              data: JSON.stringify({form:form,table:datatable}),
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
                          window.location = '{{route('promotions.discount.index')}}';
                    });
                }
               
                return false;
              }, 
              error: function(jqXHR) {
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
      $("#txtProdPrice").val(prodprice);
      $("#numProdDisc").val(0);
   }

 

  $("#btnAdd").off('click').click(function(clickEvent){
    var prodid=document.getElementById("txtProdId").value;
    var proddesc=document.getElementById("txtProdDesc").value;
    var prodprice=document.getElementById("txtProdPrice").value;
    var prodqty=1;
    var proddisc=document.getElementById("numProdDisc").value;
    if(!prodid){
       document.getElementById("wrmsg").innerHTML = 'WARNING : Siakan Pilih Produk';
       clickEvent.stopPropagation();
        return;
    }
    if(!proddisc || proddisc==='0'){
      document.getElementById("wrmsg").innerHTML = 'WARNING : Discount tidak boleh 0 atau kosong';
      clickEvent.stopPropagation();
      return;
    }

    var table=$('#dataGrid').dxDataGrid("instance");
    var objpromoprod={promo_product_qty:prodqty,promo_product_disc:proddisc}
    var objprod={product_id: prodid,
                product_shortdesc: proddesc,
                product_price:prodprice,
                promotionproducts: objpromoprod}

    table.getDataSource().store().insert(objprod);
    table.refresh();

     //mengembalikan nilai dialog
     $("#txtProdId").val("");
     $("#txtProdDesc").val("");
     $("#numProdDisc").val("0");
     $("#txtProdPrice").val("0");
     document.getElementById("wrmsg").innerHTML = '';
  });

</script>
@endsection
