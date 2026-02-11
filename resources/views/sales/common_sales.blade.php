@extends('layouts.master')
@section('content')
{{-- <body> --}}
  <div class="content" style="padding-top: 5px"> 
      <div class="row" style="margin-top: 10px">
        <!-- RightBox -->
        <div class="col-md-4  panel panel-info" style="height:500px;font-size: 14px">
          <div class="panel-body" style="height:450px;">
            <div class="box-body">
                <div class="col-sm-16" style="padding-bottom: 10px;font-size:25px">
                  <input id="txtProduct" type="text" name="" class="form-control"  placeholder="Masukan PLU/Barcode">
                </div>
              <div id="gridProduct"></div>
            </div>
          </div>
        </div>
        <!-- End RightBox -->
        <!-- LeftBox -->
        <div class="col-md-8 panel panel-info">
          <div class="panel-body" style="height:450px;">
            <div class="box-body">
              <div class="dx-texteditor-input" style='font-size:25px' id="numTotTrans"></div>
              <span id="txtTerbilang" class="text-muted">Terbilang :</span>
            </div>
            <div class="box-body" style="padding-top: 5px;">
              <div id="tabSales"></div>
            </div>
            <div class="box-body" style="padding-top: 5px;">
              <div id="btnPay"></div>
            </div>
          </div>
        </div>
      </div>
    <!-- End LeftBox -->

  {{-- add non stock dialog --}}
    <div class="modal fade" id="mdlNonStock" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <h4 id="mdlAddNonStockTitle" class="modal-title">Non Stock</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form role="form">
                <div class="box-body">
                  <div class="form-group">
                        <div class="form-group row">
                          <label for="txtNSPLUNo" class="col-sm-4 control-label text-md-right">PLU</label>
                          <div class="col-sm-8">
                          <input id="txtNSPLUNo" type="text" name="" 
                                    class="form-control"  readonly>
                          </div>
                      </div>
                      <div class="form-group row">
                          <label for="txtNSDesc" class="col-sm-4 control-label text-md-right">Deskripsi</label>
                          <div class="col-sm-8">
                          <input id="txtNSDesc" type="text" name="" 
                                    class="form-control"  readonly>
                          </div>
                      </div>
                      <div class="form-group row">
                          <label for="txtNSReff" class="col-sm-4 control-label text-md-right">Referensi</label>
                          <div class="col-sm-8">
                          <input id="txtNSReff" type="text" name="" 
                                    class="form-control">
                          </div>
                      </div>
                      <div class="form-group row"> 
                          <label for="numNSFee" class="col-sm-4 control-label text-md-right">Biaya Adm</label>
                          <div class="col-sm-2">
                            <div id="numNSFee"></div> 
                          </div>
                      </div>                       
                      <div class="form-group row">
                          <label for="numNSQty" class="col-sm-4 control-label text-md-right">Jumlah</label>
                          <div class="col-sm-2"> 
                            <div id="numNSQty"></div> 
                          </div>
                      </div>   
                      <div class="form-group row"> 
                          <label for="numNSPrice" class="col-sm-4 control-label text-md-right">Nominal</label>
                          <div class="col-sm-2">
                            <div id="numNSPrice"></div> 
                          </div>
                      </div>                        
                      <div id="tabPromo"></div>           
                      <div id="btnAddNonStock"></div>
                     </div>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
  {{-- end of non stock dialog --}}


  {{-- add payment dialog --}}
    <div class="modal fade" id="mdlAddPayment"  tabindex="-1" aria-hidden="true" data-bs-backdrop="static"  data-bs-keyboard="false" >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary"> 
            <h4 class="modal-title"><span class="badge badge-primary">Pembayaran</span></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">
            <form role="form">
                <div class="box-body">
                  <div class="form-group">
                      <div class="form-group row">
                          <label for="numTotalTrans" class="col-sm-4 control-label text-md-right">Sub Total</label>
                          <div class="col-sm-4">
                            <div class="dx-texteditor-input" id="numSubTotal"></div>
                          </div>
                      </div>
                      <div class="form-group row">
                          <label for="numTotalTrans" class="col-sm-4 control-label text-md-right">Total Discount</label>
                          <div class="col-sm-4">
                            <div class="dx-texteditor-input" id="numDiscTotal"></div>
                          </div>
                      </div>
                      <div class="form-group row">
                          <label for="numTotalTrans" class="col-sm-4 control-label text-md-right"
                            style='font-weight: 150;font-size: 20px;'>Total Transaksi</label>
                          <div class="col-sm-4">
                            <div class="dx-texteditor-input" style='font-size:30px' id="numPay"></div>
                          </div>
                      </div>
                      <div class="form-group row">
                          <div id="tabPayment"></div>
                      </div>
                      <div class="form-group row">
                        <label for="numTotPayed" class="col-sm-4 control-label text-md-right">Sisa Bayar (Kembali)</label>
                        <div class="col-sm-4">
                          <input id="numTotPayed" type="number" name="" value="0"
                                 class="form-control" value="0" placeholder="Total" readonly>
                        </div>
                       <div id="btnSave"></div>
                     </div>
                </div>
            </form>
            </div>
        </div>
      </div>
    </div>
  {{-- end of add payment dialog --}}
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
  var storeid = "{!! $storeid !!}";
  var keyProduct = document.getElementById("txtProduct");
  keyProduct.addEventListener("keypress", function(event){
      if (event.keyCode === 13) {
        var plubarcode=document.getElementById("txtProduct").value;
        var url="{{URL::to('products/find/store/')}}"+"/"+storeid+"/plu/barcode/"+plubarcode;
        $.getJSON(url,function (data){ 
           if(data.length!=0){             
              sendId(data.id,data.product_name,data.product_price,
                       data.product_buy_price,data.product_stock,1,data.product_stock_state);
              event.preventDefault();
          }else{
            Swal.fire({
              icon: 'error',
              title: "Produk Tidak ditemukan",
              showConfirmButton: false,
              timer: 1000
            });
          }
        }).fail(function( jqxhr, textStatus, error ){
          Swal.fire({
              icon: 'error',
              title: "Produk tidak ditemukan",
              showConfirmButton: false,
              timer: 1000
            });
      });
      }
  });
  keyProduct.addEventListener("keyup", function(event){
      if (event.keyCode === 13) {
        document.getElementById("txtProduct").focus();
      }
  });  
  
  var prodDataSource = new DevExpress.data.DataSource({
        load: function (key) {
            return $.ajax({
              url: "{{URL::to('products/store')}}"+"/"+storeid, 
          })
      },
  });

$("#gridProduct").dxDataGrid({
        dataSource: prodDataSource,//prods,
        showBorders: true,
        height: 500,
        searchPanel: {
            visible: true,
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
              dataField: "id",
              caption: "ID Produk",
              visible:false,
            },{
              dataField: "product_barcode",
              caption: "Barcode",
              visible:false,
            },{
              dataField: "product_name",
              caption: "Deskripsi",      
            },{
              dataField: "product_stock",
              caption: "Stok",   
              width:50,
            },{
              dataField: "product_price",
              caption: "Harga",   
              visible:false,
            },
        ],
        onRowClick:function(e){
          data=e.data; 
          sendId(data.id,data.product_name,data.product_price,
                       data.product_buy_price,data.product_stock,1,data.product_stock_state);
        },

  });
  var dataSales=[];
  $("#tabSales").dxDataGrid({
        dataSource: dataSales,
        height: 400,
        type:"array",
        keyExpr: "product_id",
        // repaintChangesOnly: true,
        rowAlternationEnabled: true,
        showBorders: true,
        scrolling: {
          mode: "virtual"
        },
        editing: {
              useIcons: true,
              allowDeleting: true,
              // allowUpdating:true,
        },
        columns: [{
                dataField: "product_id",
                caption: "ID",
                width: 100,
                visible:false,
            },{
                dataField: "product_plu",
                caption: "PLU",
                width: 100,
                visible:false,
            },{
                dataField: "product_name",
                caption: "Produk",
            },{
                dataField: "product_price",
                caption: "Harga",
                dataType: "number",
                format: "fixedPoint",
                width: 75,
            },{
                dataField: "product_qty",
                caption: "Jumlah",
                dataType: "number",
                width: 75,
            },{
                dataField: "product_disc",
                caption: "Disc(Rp)",
                dataType: "number",
                width: 75,
                visible:false,
            },{
                dataField: "product_total",
                caption: "Total",
                dataType: "number",
                format: "fixedPoint",
                width: 100,
            },{
                dataField: "product_stock_state",
                caption: "Non Stock",
                width: 75,
                visible:false,
            },{
                dataField: "reff_nonstock",
                caption: "Refferensi Non Stock",
                width: 75,
                visible:false,  
            },{
                dataField: "fee_nonstock",
                caption: "Admin Fee",
                width: 75,
                visible:false,
            },
        ],
        onContentReady: function (e) {
            moveEditColumnToLeft(e.component);
        },
        onRowRemoved: function(e){         
          var table=$('#tabSales').dxDataGrid("instance");
          $("#numTotTrans").dxNumberBox('instance').option('value',table.getTotalSummaryValue('product_total'));
          document.getElementById("txtTerbilang").innerHTML="Terbilang : "
            +terbilang($("#numTotTrans").dxNumberBox('instance').option('value'))+" rupiah";
        },
        onCellPrepared: function(e) {
          //menghitung total dengan mengambil data dari sumary tabel
          var table=$('#tabSales').dxDataGrid("instance");
          $("#numTotTrans").dxNumberBox('instance').option('value',
            table.getTotalSummaryValue('product_total'));
          document.getElementById("txtTerbilang").innerHTML="Terbilang : "
            +terbilang($("#numTotTrans").dxNumberBox('instance').option('value'))+" rupiah";
        },
        summary: {
          totalItems: [{
              column: "product_disc",
              summaryType: "sum",
              displayFormat: "Rp. {0}",
          },{
              column: "product_total",
              summaryType: "sum",
              dataType:"number",
              valueFormat: "fixedPoint",
              displayFormat: "Rp. {0}",
          }]},
    });

  $("#numTotTrans").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    height:75,
    readOnly:true,
    rtlEnabled: true,
  });
  $("#numSubTotal").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    height:25,
    readOnly:true,
    rtlEnabled: true,
  });
  $("#numDiscTotal").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    height:25,
    readOnly:true,
    rtlEnabled: true,
  });
  $("#numPay").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    height:50,
    readOnly:true,
    rtlEnabled: true,
  });

  $("#numPackQty").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    rtlEnabled: true,
  });  
  $("#numNSQty").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    rtlEnabled: true,
  }); 
  $("#numNSFee").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    rtlEnabled: true,
  });
  $("#numNSPrice").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    rtlEnabled: true,
  }); 
  function moveEditColumnToLeft(dataGrid) {
      dataGrid.columnOption("command:edit", {
          visibleIndex: -1
      });
  }

  const payments =  {!!$payments!!};
  var dataPayment=[];
  $("#tabPayment").dxDataGrid({
      dataSource: dataPayment,
      height: 250,
      // type:"array",
      // keyExpr: "pay_id",
      rowAlternationEnabled: true,
      showBorders: true,
      editing: {
          mode: 'batch',
          useIcons: true,
          allowUpdating: true,
          allowDeleting:true,
          allowAdding:true,
          popup: {
              title: "Update Pembayaran",
              showTitle: true,
              width: 700,
              height: 345,
              position: {
                  my: "top",
                  at: "top",
                  of: window
              }
          }
      },
      columns: [{
              dataField: "pay_id",
              caption: "Jenis Bayar",
              lookup: {
                   dataSource: payments,
                   displayExpr: "pay_desc",
                   valueExpr: "id",
               },
              width: 100,
          },{
              dataField: "card_no",
              caption: "Nomor Kartu",
          },{
              dataField: "card_bank_issuer",
              caption: "Bank Penerbit",
          },{
              dataField: "card_holder_name",
              caption: "Nama",
          },{
              dataField: "sale_pay_payed",
              caption: "Jumlah Bayar",
              dataType:"number",
              format: "fixedPoint",
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
              },
            ]
          },
        ],
        onRowInserted: function(e) {
          var tabpay=$('#tabPayment').dxDataGrid("instance");
          var numpay=$("#numPay").dxNumberBox('instance');
          var sisabayar=parseInt(numpay.option('value'))
                        -parseInt(tabpay.getTotalSummaryValue('sale_pay_payed'));
          $("#numTotPayed").val(sisabayar);
        },
        onRowRemoved: function(e){
          var tabpay=$('#tabPayment').dxDataGrid("instance");
          var numpay=$("#numPay").dxNumberBox('instance');
          var sisabayar=parseInt(numpay.option('value'))
                        -parseInt(tabpay.getTotalSummaryValue('sale_pay_payed'));
          $("#numTotPayed").val(sisabayar);
        },
        summary: {
          totalItems: [{
              column: "sale_pay_payed",
              summaryType: "sum",
              dataType:"number",
              valueFormat: "fixedPoint",
              displayFormat: "Total Rp. {0}",
          }]},
  });


  $("#btnAddNonStock").dxButton({
    text: "Add",
    type: "danger",
    width: 125,
    onClick: function(e) {
      var prodid=document.getElementById("txtNSPLUNo").value; 
      var prodplu=prodid.substring(5);
      var proddesc=document.getElementById("txtNSDesc").value;
      var prodqty=$("#numNSQty").dxNumberBox('instance').option('value');
      var prodprice=$("#numNSPrice").dxNumberBox('instance').option('value');
      var prodNonStockFee=$("#numNSFee").dxNumberBox('instance').option('value');
      var prodNonStockReff=document.getElementById("txtNSReff").value;
      var tabSales=$('#tabSales').dxDataGrid("instance");
      var arrSales=tabSales._controllers.data._dataSource._items; //data yang sudah ada di array tabel sales
      var prodtot=0;
      var proddisc=0;
      
      var prodqtyupd=0;

      prodtot=(prodprice*prodqty)+parseFloat(prodNonStockFee);
      for (var j = 0; j < arrSales.length; j++) {
        if(prodid==arrSales[j]['product_id']){
            prodqtyupd=arrSales[j]['product_qty']+prodqty;
            prodtot=(prodprice*prodqtyupd)+parseFloat(prodNonStockFee);
            tabSales.getDataSource().store().update(prodid,{product_qty:prodqtyupd,product_disc: proddisc,product_total: prodtot});
            tabSales.refresh();
        }
      }
      var arrmop={product_id: prodid,
                  product_plu:prodplu,
                  product_name: proddesc.concat(" (",prodNonStockReff,")"),
                  product_price: prodprice,
                  product_qty: prodqty,
                  product_disc: proddisc,
                  product_total: prodtot,
                  reff_nonstock: prodNonStockReff,
                  fee_nonstock:prodNonStockFee}
     
      tabSales.getDataSource().store().insert(arrmop);  
      tabSales.refresh();
      $("#txtNSPLUNo").val("");  
      $("#txtNSDesc").val("");
      $("#txtNSReff").val("");
      $("#numNSQty").dxNumberBox('instance').option('value',0);
      $("#numNSPrice").dxNumberBox('instance').option('value',0);
      $("#numNSFee").dxNumberBox('instance').option('value',0);
      $('#mdlNonStock').modal('hide');
    }
  });

  $("#btnPay").dxButton({
    text: "Pembayaran",
    type: "default",
    width: 125,
    onClick: function(e) {

      //periksan ke tabel promosi apakah ada promo yang aktif untuk pembelian dengan
      //nominal tertentu mendapatkan hadiah
      //periksa promo dengan type 3

      $("#btnPay").dxButton("instance").option("disabled",true);
      var table=$('#tabSales').dxDataGrid("instance");
      var subtotal=table.getTotalSummaryValue('product_total');
      $("#numSubTotal").dxNumberBox('instance').option('value',subtotal);

      var disctotal=$("#numDiscTotal").dxNumberBox('instance').option('value');
      disctotal=disctotal+table.getTotalSummaryValue('product_disc');

      var total=subtotal-disctotal;

      // $("#numPay").dxNumberBox('instance').option('value',$("#numTotTrans").dxNumberBox('instance').option('value'));
      $("#numDiscTotal").dxNumberBox('instance').option('value',disctotal);
      $("#numPay").dxNumberBox('instance').option('value',total);
      $('#mdlAddPayment').modal('show');
      $("#numTotPayed").val($("#numPay").dxNumberBox('instance').option('value'));
    }
  });
  
  $("#btnSave").dxButton({
    text: "Simpan Transaksi",
    type: "default",
    width: 200,
    onClick: function(e) {
      var table=$('#tabPayment').dxDataGrid("instance");
      var arrProd=table.getDataSource().items();
      var sisabayar=document.getElementById("numTotPayed").value;
      // getprodbydesc("ALL"); //reload data list produk
      if(arrProd.length==0){
        Swal.fire({
              icon: 'error',
              title: "Daftar Pembayaran masih kosong...",
              showConfirmButton: false,
              timer: 1000
            });
        return false;
      }
      if(sisabayar>0){
        Swal.fire({
              icon: 'error',
              title: "Pembayaran Belum Selesai...",
              showConfirmButton: false,
              timer: 1000
            });
        return false;
      }
      var dataProd = dataSales;
      var dataPay = dataPayment; 
      var disctotal=$("#numDiscTotal").dxNumberBox('instance').option('value');
      var salestotal=$("#numPay").dxNumberBox('instance').option('value');
      $("#btnSave").dxButton("instance").option("disabled",true);
      memberno='NOTMEMBER';
      $.ajax({
          type: "POST",
          url: "{{route('sales.store')}}",
          data: JSON.stringify({memberid:memberno, storeid:storeid, sale_disc:disctotal,sale_total:salestotal,tabproduct:dataProd,tabpay:dataPay}),
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          success: function(data){
            // console.log(response);
            // setTimeout(function(){ location.reload(); }, 5000);
            const salesid=data.message;
            const url="{{URL::to('farma/reports/sales/common/pdf/receipt')}}"+"/"+salesid;
            window.location = url;
          },
          failure: function(data) {
              alert(data.message);
          },
      });
    }
  });
});

function sendId(prodid,proddesc,prodprice,prodbuyprice,prodstock,prodqty,prodns){
  //stok nol dari daftar
  if (prodns=="1" && prodstock==0){
    Swal.fire({
        icon: 'error',
        title: "Stok ".concat(proddesc," Kosong"),
        showConfirmButton: false,
        timer: 1000
    });
    return false;
  }

  // perlakuan harga 
  // 1. harga khusus member, berarti harus di abaikan promosi yang berjalan
  // 2. harga promosi, tidak berlaku untuk member
  // perlakuan non stok, khusus untuk barang dengan status non stok maka jumlah stok akan diabaikan
  // transaksi bisa dilanjutkan
  if(prodns=='0'){
    $("#txtNSPLUNo").val(prodid)  
    $("#txtNSDesc").val(proddesc);
    $("#numNSQty").dxNumberBox('instance').option('value',1);
    $("#numNSPrice").dxNumberBox('instance').option('value',prodprice);
    $('#mdlNonStock').modal('show');
    return false;
  }

  //validasi
  if (prodid==""){
    Swal.fire({
        icon: 'error',
        title:  "Produk belum tersedia, silakan masukan produk...",
        showConfirmButton: false,
        timer: 1000
    });
    return false;
  }
  if (prodprice==0){
    Swal.fire({
        icon: 'error',
        title: "Harga Produk belum tersedia, silakan perbaharui terlebih dahulu...",
        showConfirmButton: false,
        timer: 1000
    }); 
    return false;
  }

  var prodqty=parseInt(prodqty); 
  var proddisc=0;
  var discmargin=0;
  var prodtot=prodprice*prodqty;
  var objSalesTable;
  objSalesTable={product_id:prodid,product_name:proddesc,product_price:prodprice,
                  product_qty:prodqty,product_disc:discmargin,product_total:prodtot,
                  product_stock:prodstock,product_stock_state:prodns}
  addtosalestable(objSalesTable);
}



function addtosalestable(objSalesTable){
  var table=$('#tabSales').dxDataGrid("instance");
  var arrItem=table._controllers.data._dataSource._items; //data yang sudah ada di array tabel
  var tottrans=$("#numTotTrans").dxNumberBox('instance').option('value');
  var disc=0;
  var prodid=objSalesTable.product_id;
  var prodplu="";//prodid.substring(5);
  var prodstock=objSalesTable.product_stock;
  // perhitungan discount adalah dari discount total, jadi di faktur tidak di kalikan lagi dengn qty
  // faktur =qty x harga jual - discount

  var arrmop={product_id: prodid,
              product_plu:prodplu,
              product_name:objSalesTable.product_name,
              product_price:objSalesTable.product_price,
              product_qty:objSalesTable.product_qty,
              product_disc:objSalesTable.product_disc,
              product_total:objSalesTable.product_total,
              product_stock_state:objSalesTable.product_stock_state}

  var prodqtyupd=0;
  const prodns=objSalesTable.product_stock_state;
  if (arrItem.length != 0) {
    tottrans=0;
    for (var i = 0; i <arrItem.length; i++) {
      if(prodid==arrItem[i]['product_id']){
          prodqtyupd=arrItem[i]['product_qty']+objSalesTable.product_qty;
          if(prodqtyupd>prodstock && prodns=='1'){
            Swal.fire({
                icon: 'error',
                title: "Stok Produk tidak tersedia....",
                showConfirmButton: false,
                timer: 1000
            });  
            return false;
          }
          disc=arrItem[i]['product_disc']+objSalesTable.product_disc;
          prodtot=(objSalesTable.product_price*prodqtyupd)-disc;
          table.getDataSource().store().update(objSalesTable.product_id,{product_qty:prodqtyupd,
                product_disc:disc, product_total: prodtot});
          table.refresh();
      }         
    }
  }
  table.getDataSource().store().insert(arrmop);
  table.refresh();
}


function terbilang(x){
	  var abil = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
	  if (x < 12)
	    return " " + abil[x];
	  else if (x < 20)
	    return terbilang(x - 10) + "belas";
	  else if (x < 100)
	    return terbilang(Math.floor(x / 10)) + " puluh" + terbilang(x % 10);
	  else if (x < 200)
	    return " seratus" + terbilang(x - 100);
	  else if (x < 1000)
	    return terbilang(Math.floor(x / 100)) + " ratus" + terbilang(x % 100);
	  else if (x < 2000)
	    return " seribu" + terbilang(x - 1000);
	  else if (x < 1000000)
	    return terbilang(Math.floor(x / 1000)) + " ribu" + terbilang(x % 1000);
	  else if (x < 1000000000)
	    return terbilang(Math.floor(x / 1000000)) + " juta" + terbilang(x % 1000000);
}
</script>
@endsection