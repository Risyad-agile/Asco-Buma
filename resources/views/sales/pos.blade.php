@extends('layouts.master')
@section('content')
{{-- <body> --}}
  <div class="content">
  <!-- <div class="content"> -->
      <form id="form-container" role="form">
          <!-- <div class="box-body"> -->
            <div class="form-group">
            <div class="form-group row">
              <label for="txtMember" class="col-sm-4 control-label text-md-right">Pilih atau Masukan Member(Kode/Nama)</label>
              <div class="col-sm-4">
                <div id="memberSelectBox"></div>
                <div id="numMemberDisc"></div>
              </div>
            </div>
            </div>
          <!-- </div> -->
      </form>

      <div class="row">
        <nav class="card col-md-4" style="height:475px;font-size: 14px">
          <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" 
              role="tab" aria-controls="nav-home" aria-selected="true">Entry Cepat</a>
            <a class="nav-item nav-link" id="nav-cari-tab" data-toggle="tab" href="#nav-cari" 
              role="tab" aria-controls="nav-cari" aria-selected="false">Pencarian</a>
          </div>
          <div class="tab-content" id="nav-tabContent">
          <!-- RightBox -->
            <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
              <div class="form-group">
                <label for="txtProduct">Produk</label>
                <input type="text" class="form-control form-control-sm" id="txtProduct"  
                        placeholder="Cari Kode atau Nama atau Barcode Produk">
              </div>
              <div class="form-group">
                <div class="form-group row">
                  <label for="txtProdId" class="col-sm-4 control-label">ID Produk</label>
                  <div class="col-sm-8">
                    <input id="txtProdId" type="text" name="{{'product_id'}}"
                            class="form-control form-control-sm" placeholder="Product ID" readonly>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="txtProdDesc" class="col-sm-4 control-label">Produk</label>
                  <div class="col-sm-8">
                    <input id="txtProdDesc" type="text" name="{{'product_shortdesc'}}"
                            class="form-control form-control-sm" placeholder="Description" readonly>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="numProdStock" class="col-sm-4 control-label">Stok Tersedia</label>
                  <div class="col-sm-8">
                    <input id="numProdStock" type="number" min="0" name="{{'product_qty'}}"
                          class="form-control form-control-sm" placeholder="Quantity" value="0" readonly>
                  </div>
              </div>
              <div class="form-group row">
                  <label for="numProdPrice" class="col-sm-4 control-label">Harga</label>
                  <div class="col-sm-8">
                    <input id="numProdPrice" type="number" min="0" name="{{'prod_price'}}"
                          class="form-control form-control-sm" value="0" placeholder="Price" readonly>
                    <input id="numProdBuyPrice" type="hidden" min="0" name="{{'prod_buy_price'}}"
                          class="form-control form-control-sm" value="0" placeholder="Price" readonly>
                  </div>
              </div>
              <div class="form-group row">
                  <label for="numProdQty" class="col-sm-4 control-label">Jumlah</label>
                  <div class="col-sm-8">
                    <input id="numProdQty" type="number" min="0" name="{{'prod_qty'}}"
                          class="form-control form-control-sm" value="0" placeholder="Quantity">
                  </div>
              </div>
              <div class="form-group row">
                  <label for="numProdTotal" class="col-sm-4 control-label">Total</label>
                  <div class="col-sm-8">
                    <input id="numProdTotal" type="number" name="" value="0"
                          class="form-control form-control-sm" value="0" placeholder="Total" readonly>
                  </div>
                  <input id="nonStockState" type="hidden" name="" value="0"
                          class="form-control form-control-sm"  placeholder="Non Stock">
              </div>
            </div>
          </div>
          <!-- End RightBox -->
          <div class="tab-pane fade" id="nav-cari" role="tabpanel" aria-labelledby="nav-cari-tab">
            <div class="box-body" style="padding-top: 5px;">
              <div id="gridProduct"></div>
            </div>
        </div>
        </nav>
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


{{-- choose promo dialog --}}
<div class="modal fade" id="mdlChoosePromo" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <!-- <h4 id="mdlAddPromoTitle" class="modal-title">Promo</h4> -->
          <h3 class="modal-title"><span class="badge badge-primary">Produk Terdaftar Pada Promo Berikut</span></h3>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
            <div id="promoList"></div>
      </div>
    </div>
  </div>
</div>
{{-- end of promo dialog --}}

{{-- <div id="popup">
  <p>Popup content</p>
</div> --}}


  {{-- add promo dialog --}}
    <div class="modal fade" id="mdlPromo" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <!-- <h4 id="mdlAddPromoTitle" class="modal-title">Promo</h4> -->
            <h3 class="modal-title"><span class="badge badge-primary">Produk Promo</span></h3>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <form role="form">
                <div class="box-body">
                  <div class="form-group">
                        <div class="form-group row">
                          <label for="txtPromoNo" class="col control-label">Promo ID</label>
                          <div class="col">
                          <input id="txtPromoNo" type="text" name="" 
                                    class="form-control"  readonly>
                          <input id="txtPromoType" type="hidden" name="" 
                                    class="form-control">
                          </div>
                          <div class="col"></div>
                          <div class="col"></div>
                      </div>
                      <div class="form-group row">
                          <label for="txtPromoDesc" class="col control-label">Promo </label>
                          <div class="col-sm-6">
                          <input id="txtPromoDesc" type="text" name="" 
                                    class="form-control"  readonly>
                          </div>
                          <div class="col"></div>
                      </div>
                      <div class="form-group row">
                          <label for="numPromoPrice" class="col control-label">Harga Promo</label>
                          <div class="col">
                              <div id="numPromoPrice"></div> 
                          </div>
                          <div class="col"></div>
                          <div class="col"></div>
                      </div>  
                      <div class="form-group row">
                          <label for="numPackQty" class="col control-label">Jumlah Paket</label>
                          <div class="col">
                            <div id="numPackQty"></div> 
                          </div>
                          <div class="col"></div>
                          <div class="col"></div>
                      </div>    
                      <div id="tabPromo"></div>           
                      <div id="btnUsePromo"></div>
                      <div id="btnSkipPromo"></div>
                     </div>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
  {{-- end of choose promo dialog --}}

  {{-- add non stock dialog --}}
    <div class="modal fade" id="mdlNonStock" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary">
            <h4 id="mdlAddNonStockTitle" class="modal-title">Non Stock</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
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
    <div class="modal fade" id="mdlAddPayment" role="dialog">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary"> 
            <h4 class="modal-title"><span class="badge badge-primary">Pembayaran</span></h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
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
  {{-- end of add payment dialog --}}
  <!-- </div> -->
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
  var members={!!$members!!}
  $("#memberSelectBox").dxSelectBox({
      dataSource: members,
      showClearButton: true,
      searchEnabled: true,
      searchExpr: ["member_no", "member_name"],
      displayExpr: "member_name",
      valueExpr: "member_no",
      onValueChanged: function(data) {
        if(data.component.option("selectedItem")==null){
          location.reload();
        }
        else{
          var discount=data.component.option("selectedItem").member_discount;
          $("#numMemberDisc").dxNumberBox('instance').option('value',discount);
        }
        $("#memberSelectBox").dxSelectBox('instance').option("disabled",'true'); //menjaga integritas data
      }
  });
  $("#numMemberDisc").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    // height:50,
    visible:false,
    readOnly:true,
    rtlEnabled: true,
  });
  $("#numTotTrans").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    height:50,
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
  $("#numPromoPrice").dxNumberBox({
    format: "#,##0.##",
    value: 0,
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

  var dataSales=[];
  $("#tabSales").dxDataGrid({
        dataSource: dataSales,
        height: 350,
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
            },{
                dataField: "product_total",
                caption: "Total",
                dataType: "number",
                format: "fixedPoint",
                width: 100,
            },{
              dataField: "product_stockstate",
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
          var promotype=document.getElementById("txtPromoType").value;
          console.log(promotype);
          if(promotype!=""){
            DevExpress.ui.notify({
            message: "sebagian produk promo di hapus, page reload",
            position: {
                my: "center top",
                at: "center top"
            }
            }, "error", 1000);     
            location.reload(); //stok tidak up to date, halaman pos di refresh
          }

          
          var table=$('#tabSales').dxDataGrid("instance");
          $("#numTotTrans").dxNumberBox('instance').option('value',
            table.getTotalSummaryValue('product_total'));
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

  var dataPromo=[];
  $("#tabPromo").dxDataGrid({
      dataSource: dataPromo,
      height: 150,
      type:"array",
      keyExpr: "product_id",
      rowAlternationEnabled: true,
      showBorders: true,
      columns: [{
              dataField: "product_id",
              caption: "ID",
              // width: 100,
              // visible:false,
          },{
              dataField: "product_plu",
              caption: "PLU",
              visible:false,
            },{
              dataField: "product_name",
              caption: "Produk",
          },{
              dataField: "promo_product_qty",
              caption: "Jumlah",
              // width: 80,
          },{
              dataField: "promo_product_price",
              caption: "Harga Promo",
              // width: 125,
              visible:false,
          },{
              dataField: "product_stock",
              caption: "Stock",
              width: 75,
              visible:false,
          },{
              dataField: "product_price",
              caption: "Harga Normal",
              width: 125,
              visible:false,    
          },{
              dataField: "promo_product_state",
              caption: "Stat Produk",
              width: 75,
              visible:false,
          },{
              dataField: "product_stockstate",
              caption: "Stat Produk",
              width: 75,
              visible:false,
          },
        ],
  });

  var dataPayment=[];
  $("#tabPayment").dxDataGrid({
      dataSource: dataPayment,
      height: 250,
      type:"array",
      keyExpr: "pay_id",
      rowAlternationEnabled: true,
      showBorders: true,
      editing: {
          mode: "popup",
          useIcons: true,
          // allowUpdating: true,
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
                   dataSource: [{"pay_id":"1","pay_desc":"Tunai"},
                                {"pay_id":"2","pay_desc":"Kartu Debit"},
                                {"pay_id":"3","pay_desc":"Kartu Kredit"},
                                {"pay_id":"4","pay_desc":"Kartu Anggota"},
                                {"pay_id":"5","pay_desc":"Transfer"},
                                {"pay_id":"6","pay_desc":"Voucher"}],
                   displayExpr: "pay_desc",
                   valueExpr: "pay_id",
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

  $("#btnUsePromo").dxButton({
      text: "Use Promo",
      type: "default",
      width: 125,
      onClick: function(e) {
        var packqty=$("#numPackQty").dxNumberBox('instance').option('value');      
        if (parseInt(packqty)==0){
          DevExpress.ui.notify({
              message: "Jumlah Pack tidak boleh kosong....",
              position: {
                  my: "center top",
                  at: "center top"
              }
          }, "warning", 3000);
          return false;
        }

        var tottrans=$("#numTotTrans").dxNumberBox('instance').option('value');
        var promotype=document.getElementById("txtPromoType").value;
                
        var tabSales=$('#tabSales').dxDataGrid("instance");
        var arrSales=tabSales._controllers.data._dataSource._items; //data yang sudah ada di array tabel sales

        var tabPromo=$('#tabPromo').dxDataGrid("instance");
        var arrPromo=tabPromo._controllers.data._dataSource._items; //data yang sudah ada di array tabel promo

        //harga produk utama adalah harga promo, sementara itu harga produk free menjadi nol
        //jumlah promo adalah sesuai dengan packqty yang dimasukan (dikali dengan ini promo)
        var arrmop;
        var prodqty=0;
        var promotot=0;
        var prodpromotot=0;
        var proddisc=0;
        var prodqtyupd=0;
        var prodpromoqty=0;
        // var prodpromoprice=0;
        var prodpromotot=0;
        var disc=0;

        //UPDATE 06 JULI 2019
        //kondisi yang memungkinkan di promo ini adalah
        //1. ketika produk promo hanya 1 baris, promo type=1 (discount) ini berarti produk dengan harga khusus,
        //   konsepnya mengabaikan harga produk, dan mengacu kepada harga promo
        //   hanya ada satu baris produk, sudah di batasi pada saat input promo
        var promoprice=$("#numPromoPrice").dxNumberBox('instance').option('value');
        var prodid="";
        var proddesc="";
        var prodprice=0;
        var prodstock=0
        var promoprodqty=0;
        var prodss="";
        var total=0;

        if(promotype=="1"){  //promo dicount
            prodid=arrPromo[0]['product_id'];
            proddesc=arrPromo[0]['product_name'];
            prodprice=arrPromo[0]['promo_product_price'];
            prodstock=arrPromo[0]['product_stock'];
            promoprodqty=arrPromo[0]['promo_product_qty'];
            prodss=arrPromo[0]['product_stockstate'];


            promotot=promoprice*packqty; //total harga promo
            prodqty=promoprodqty*packqty; //jumlah yang dikeluarkan
            prodpromotot=prodqty*prodprice; //total harga barang promo
            proddisc=prodpromotot-promotot; //dicount produk promo
            if(prodss==1){ //produk stok
              if(prodqty>prodstock){
                  DevExpress.ui.notify({
                  message: "Stok ".concat(proddesc," tidak mencukupi"),
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                  }, "warning", 3000);
                  return false;
              }
            }



            // $("#numSubTotal").dxNumberBox('instance').option('value',prodpromotot);
            $("#numDiscTotal").dxNumberBox('instance').option('value',0);//set nilai awal

            var objSalesTable={product_id:prodid,product_name:proddesc,product_price:prodprice,
                    product_qty:prodqty,product_disc:proddisc,product_total:prodpromotot,
                    product_stock:prodstock,product_stockstate:prodss}
            addtosalestable(objSalesTable);
            $('#mdlPromo').modal('hide');
            return;
        }


        //2. ketika ada lebih dari 1 baris, promo type=2 (buy one get something) ini berarti produk dengan hadiah
        //   atau ada produk lain yang terlibat dalam hal pembuatan paket promosi
        //   konsepnya menggunakan harga promo sebagai total sales disimpan di header
        //   discount adalah perhitungan pengurangan total barang dengan harga barang oleh harga promo 
        //   nilai dicount di simpan di header, sementara itu harga pada promo tidak berubah
        if(promotype=="2"){  //promo buy one get something
          for (let i = 0; i< arrPromo.length; i++) {
            prodid=arrPromo[i]['product_id'];
            proddesc=arrPromo[i]['product_name'];
            prodprice=arrPromo[i]['promo_product_price'];
            prodstock=arrPromo[i]['product_stock'];
            promoprodqty=arrPromo[i]['promo_product_qty'];
            prodss=arrPromo[i]['product_stockstate'];
            prodqty=promoprodqty*packqty; //jumlah yang dikeluarkan
            if(prodss==1){ //produk stok
              if(prodqty>prodstock){
                  DevExpress.ui.notify({
                    message: "Stok ".concat(proddesc," tidak mencukupi"),
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                  }, "warning", 3000);
                  return false;
              }
            }
            prodqty=promoprodqty*packqty; //jumlah yang dikeluarkan
            prodpromotot=prodqty*prodprice; //total harga barang promo
            proddisc=0; //tidak ada discount per produk

            var objSalesTable={product_id:prodid,product_name:proddesc,product_price:prodprice,
                      product_qty:prodqty,product_disc:proddisc,product_total:prodpromotot,
                      product_stock:prodstock,product_stockstate:prodss}
            addtosalestable(objSalesTable);
            total=total+prodpromotot;
          }
        }
        var disctotal=$("#numDiscTotal").dxNumberBox('instance').option('value');
        promotot=promoprice*packqty; //total harga promo       
        proddisc=disctotal+(total-promotot);
        $("#numDiscTotal").dxNumberBox('instance').option('value',proddisc);
        $('#mdlPromo').modal('hide');
    }
  });  

  $("#btnSkipPromo").dxButton({
    text: "Skip Promo",
    type: "danger",
    width: 125,
    onClick: function(e) {

      // proses skip promo, paket berisi nilai yang berasal jumlah, jika tidak ikut promo maka jumlah 
      // tersebut yang digunakan 
      var prodqty=$("#numPackQty").dxNumberBox('instance').option('value');//1; //karena di skip hanya di tambah 1

      var tottrans=$("#numTotTrans").dxNumberBox('instance').option('value');
      var tabSales=$('#tabSales').dxDataGrid("instance");
      var arrSales=tabSales._controllers.data._dataSource._items; //data yang sudah ada di array tabel sales

      var tabPromo=$('#tabPromo').dxDataGrid("instance");
      var arrPromo=tabPromo._controllers.data._dataSource._items; //data yang sudah ada di array tabel promo
      var proddisc=0;     

      var prodid=arrPromo[0]['product_id'];
      var proddesc=arrPromo[0]['product_name'];
      var prodprice=arrPromo[0]['promo_product_price'];
      var prodstock=arrPromo[0]['product_stock'];
      var prodns='1';// sementara dianggap stok
      var prodtot=prodprice*prodqty; //total harga
      
      var objSalesTable={product_id:prodid,product_name:proddesc,product_price:prodprice,
                     product_qty:prodqty,product_disc:proddisc,product_total:prodtot,
                     product_stock:prodstock,product_stockstate:prodns}
      addtosalestable(objSalesTable);
      $('#mdlPromo').modal('hide');
    }
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
      $("#btnSave").dxButton("instance").option("disabled",true);
      var table=$('#tabPayment').dxDataGrid("instance");
      var arrProd=table.getDataSource().items();
      var sisabayar=document.getElementById("numTotPayed").value;
      // getprodbydesc("ALL"); //reload data list produk
      if(arrProd.length==0){
        DevExpress.ui.notify({
                  message: "Daftar Pembayaran masih kosong...",
                  position: {
                      my: "center top",
                      at: "center top"
                  }
              }, "warning", 3000);
        return false;
      }
      if(sisabayar>0){
        DevExpress.ui.notify({
                  message: "Pembayaran Belum Selesai...",
                  position: {
                      my: "center top",
                      at: "center top"
                  }
              }, "warning", 3000);
        return false;
      }
      var dataProd = dataSales;
      var dataPay = dataPayment;
      var memberno =$('#memberSelectBox').dxSelectBox("instance").option("value");
      var disctotal=$("#numDiscTotal").dxNumberBox('instance').option('value');
      var salestotal=$("#numPay").dxNumberBox('instance').option('value');
      if(memberno==null){
        memberno='NOTMEMBER';
      }
      $.ajax({
          type: "POST",
          url: "{{route('sales.store')}}",

          // data: JSON.stringify({ form: form,table:data}),
          data: JSON.stringify({memberno:memberno, sale_disc:disctotal,sale_total:salestotal, tabproduct:dataProd,tabpay:dataPay}),
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          beforeSend: function()
          {
              //do before send
             
          },
          success: function(response){
            // console.log(response);
            // setTimeout(function(){ location.reload(); }, 5000);
          },
          failure: function(errMsg) {
              alert(errMsg);
          },
          complete: function(jqXHR) {
            if(jqXHR.readyState === 4) {
                DevExpress.ui.notify({
                    message: "Transaksi Penjualan Berhasil di Simpan",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "success", 3000);
                var salesno=jqXHR.responseText;
                var url="{{URL::to('store/reports/salesinv')}}"+"/"+salesno;
                window.location = url;
            }
          },
      });
    }
  });
});

function sendId(prodid,proddesc,prodprice,prodbuyprice,prodstock,prodqty,prodns){
  //stok nol dari daftar
  if (prodns=="1" && prodstock==0){
      DevExpress.ui.notify({
          message: "Stok ".concat(proddesc," Kosong"),
          position: {
              my: "center top",
              at: "center top"
          }
      }, "warning", 3000);
      return false;
  }

  // perlakuan harga 
  // 1. harga khusus member, berarti harus di abaikan promosi yang berjalan
  // 2. harga promosi, tidak berlaku untuk member

  // perlakuan non stok, khusus untuk barang dengan status non stok maka jumlah stok akan diabaikan
  // transaksi bisa dilanjutkan
  if(prodns=='1'){
    var url="{{URL::to('store/sales/getproductlivestock')}}"+"/"+prodid;
    $.getJSON(url,function (data){
      //stok nol akibat pengurangan sales berjalan
      if (data==0){
        DevExpress.ui.notify({
            message: "Stok ".concat(proddesc," tidak mencukupi"),
            position: {
                my: "center top",
                at: "center top"
            }
        }, "warning", 3000);
        return false;
      }
      if (data!=prodstock)
      {
        DevExpress.ui.notify({
            message: "Stok Produk tidak sesuai, halaman akan di refresh",
            position: {
                my: "center top",
                at: "center top"
            }
        }, "error", 1000);     
        location.reload(); //stok tidak up to date, halaman pos di refresh
      }
    }).fail(function(jqxhr, textStatus, error){
        DevExpress.ui.notify({
            message: "Stok Produk tidak sesuai, halaman akan di refresh",
            position: {
                my: "center top",
                at: "center top"
            }
        }, "error", 1000);
        location.reload(); //stok tidak up to date, halaman pos di refresh
    });
  }else{
    $("#txtNSPLUNo").val(prodid)  
    $("#txtNSDesc").val(proddesc);
    $("#numNSQty").dxNumberBox('instance').option('value',1);
    $("#numNSPrice").dxNumberBox('instance').option('value',prodprice);
    $('#mdlNonStock').modal('show');
    return false;
  }


  //validasi
  if (prodid==""){
    DevExpress.ui.notify({
        message: "Produk belum tersedia, silakan masukan produk...",
        position: {
            my: "center top",
            at: "center top"
        }
    }, "warning", 3000);
    return false;
  }
  if (prodprice==0){
    DevExpress.ui.notify({
        message: "Harga Produk belum tersedia, silakan perbaharui terlebih dahulu...",
        position: {
            my: "center top",
            at: "center top"
        }
    }, "warning", 3000);
    return false;
  }

  var prodqty=parseInt(prodqty);
  var memberid=$('#memberSelectBox').dxSelectBox("instance").option("value");
  var promono=document.getElementById("txtPromoNo").value;
  var proddisc=0;
  var discmargin=0;
  var prodtot=prodprice*prodqty;
  var objSalesTable;
  //cek apakah member atau bukan
  //jika member tidak mengikuti promo
  if(memberid!=null){   //transaksi member
      proddisc=parseFloat($("#numMemberDisc").dxNumberBox('instance').option('value'));
      discmargin=((prodprice-prodbuyprice)*proddisc)*prodqty;
      prodtot=(prodprice*prodqty)-discmargin;
      objSalesTable={product_id:prodid,product_name:proddesc,product_price:prodprice,
                     product_qty:prodqty,product_disc:discmargin,product_total:prodtot,
                     product_stock:prodstock,product_stockstate:prodns}
      addtosalestable(objSalesTable);
  }else{  //jika bukan member mengikut promo
      //Produk yang dikirim adalah yang sudah di cari di tabel promotion
      //bisa single row (harga discount) 
      //bisa juga multi row (free product lain) 
      //jumlah produk juga berdasarkan yang ada di promosi

      
      var url="{{URL::to('store/sales/getpromos')}}"+"/"+prodid;  
      $.getJSON(url,function (data){
        if(data[0]=="NOTPROMO"){ 
            objSalesTable={product_id:prodid,product_name:proddesc,product_price:prodprice,
                     product_qty:prodqty,product_disc:discmargin,product_total:prodtot,
                     product_stock:prodstock,product_stockstate:prodns}
            addtosalestable(objSalesTable);
            return;
        }

        var listWidget = $("#promoList").dxList({
          dataSource: data,
          itemTemplate: function(data, index) {
            return data.promo_rule;
          },
          selectionMode: "single",
          height: 200,
          onItemClick: function(data) {
              var promotype=document.getElementById("txtPromoType").value;
              
              var promo={};
              promo=listWidget.option("selectedItem");
              if(promotype!=""){
                if(promotype!=promo.promo_type){
                  DevExpress.ui.notify({
                      message: "Tidak diperkenankan satu struk dua promo barang yang sama",
                      position: {
                          my: "center top",
                          at: "center top"
                      }
                  }, "error", 1000);
                  return;
                }
              }
              setPromoDialog(promo,prodstock);
              $('#mdlChoosePromo').modal('hide');
          }
        }).dxList("instance");
        $('#mdlChoosePromo').modal('show');
    });
  }
}

function setPromoDialog(promo,prodstock){
    $("#txtPromoNo").val(promo.promo_no)  
    $("#txtPromoDesc").val(promo.promo_desc); 
    $("#txtPromoType").val(promo.promo_type);
    $("#numPromoPrice").dxNumberBox('instance').option('value',promo.promo_price);
    $("#numPackQty").dxNumberBox('instance').option('value',1);
    
    var arrItemPromo;
    var tablePromo=$('#tabPromo').dxDataGrid("instance"); 
    tablePromo.option('dataSource', []); //memastikan tabel kosong
    for (i in promo.products) {
      arrItemPromo={product_id: promo.products[i].product_id,
                  product_plu: promo.products[i].product_plu,
                  product_name: promo.products[i].product_name,
                  promo_product_price: promo.products[i].promotionproducts.promo_product_price,
                  promo_product_qty: promo.products[i].promotionproducts.promo_product_qty,
                  product_stock:prodstock, product_stockstate:promo.products[i].product_stockstate,
                  promo_product_state:promo.products[i].promotionproducts.promo_product_state}
      tablePromo.getDataSource().store().insert(arrItemPromo);
    }
    tablePromo.refresh();   
    $('#mdlPromo').modal('show');
}
</script>

<script>
var keyProduct = document.getElementById("txtProduct");
keyProduct.addEventListener("keypress", function(event){
    if (event.keyCode === 13) {
      var kunci=document.getElementById("txtProduct").value;
      var url="{{URL::to('products/searchproductbykriteria')}}"+"/"+kunci;
      $.getJSON(url,function (data){
          $("#txtProdId").val(data[0].product_id);
          $("#txtProdDesc").val(data[0].product_shortdesc);
          $("#numProdPrice").val(data[0].product_price);
          $("#numProdBuyPrice").val(data[0].product_buy_price);
          $("#numProdStock").val(data[0].product_stock);
          $("#nonStockState").val(data[0].product_stockstate);
          $("#numProdQty").val("");
          event.preventDefault();
      }).fail(function( jqxhr, textStatus, error ){
        DevExpress.ui.notify({
              message: "Produk tidak ditemukan",
              position: {
                  my: "center top",
                  at: "center top"
              }
          }, "error", 3000);
    });
    }
});
keyProduct.addEventListener("keyup", function(event){
    if (event.keyCode === 13) {
      document.getElementById("numProdQty").focus();
    }
});
var keyProdQty = document.getElementById("numProdQty");
keyProdQty.addEventListener("keypress", function(event){
    var prodid=document.getElementById("txtProdId").value;
    var proddesc=document.getElementById("txtProdDesc").value;
    var prodprice=document.getElementById("numProdPrice").value;
    var prodbuyprice=document.getElementById("numProdBuyPrice").value;
    var prodqty=document.getElementById("numProdQty").value;
    var prodstock=document.getElementById("numProdStock").value;
    var prodns=document.getElementById("nonStockState").value; 
    $("#numProdTotal").val(prodprice*prodqty);
    if (event.keyCode === 13) {
      sendId(prodid,proddesc,prodprice,prodbuyprice,prodstock,prodqty,prodns);
    }
})
keyProdQty.addEventListener("keyup", function(event){
    if (event.keyCode === 13) {
      $("#txtProduct").val("");
      $("#txtProdId").val("");
      $("#txtProdDesc").val("");
      $("#numProdPrice").val(0);
      $("#numProdBuyPrice").val(0);
      $("#numProdStock").val(0);
      $("#numProdQty").val("");
      $("#numProdTotal").val(0);
      document.getElementById("txtProduct").focus();
    }
});
var storeid = "{!! $storeid !!}";
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
        height: 400,
        searchPanel: {
            visible: true,
            // width: 400,
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
            },{
              dataField: "product_barcode",
              caption: "Barcode",
              // width:125,
              visible:false,
            },{
              dataField: "product_name",
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
        onRowClick:function(e){
          data=e.data;
          sendId(data.product_id,data.product_name,data.product_price,
                       data.product_buy_price,data.product_stock,1,data.product_stockstate);
        },
        onSelectionChanged: function (selectedItems) {
          // console.log(e.data);
          // $("#popup").dxPopup({
          //     title: "Popup Title",
          //     visible: true
          // });
        },
        onToolbarPreparing: function (e) {  
          var toolbarItems = e.toolbarOptions.items;  
          var searchPanel = $.grep(toolbarItems, function (item) {  
              return item.name === "searchPanel";  
          })[0];  
          searchPanel.location = "before";  
        }
  });
</script>

<script>
function addtosalestable(objSalesTable){
  var table=$('#tabSales').dxDataGrid("instance");
  var arrItem=table._controllers.data._dataSource._items; //data yang sudah ada di array tabel
  var tottrans=$("#numTotTrans").dxNumberBox('instance').option('value');
  var disc=0;
  var prodid=objSalesTable.product_id;
  var prodplu=prodid.substring(5);
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
              product_stockstate:objSalesTable.product_stockstate}

  var prodqtyupd=0;
      if (arrItem.length != 0) {
        tottrans=0;
        for (var i = 0; i <arrItem.length; i++) {
          if(prodid==arrItem[i]['product_id']){
              prodqtyupd=arrItem[i]['product_qty']+objSalesTable.product_qty;
              if(prodqtyupd>prodstock && prodns=='0'){
                DevExpress.ui.notify({
                    message: "Stok Produk tidak tersedia....",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
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