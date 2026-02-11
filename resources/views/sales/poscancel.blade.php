@extends('layouts.master')
@section('content')
<div class="form">
    <div class="card">
        <div class="card-body">
            <label>Masukan Nomor Sales (SALENO)</label>
            <div class='form- row'>
                <div class="col-12 col-md-9 mb-2 mb-md-0">
                    <div  id="txtSalesNo"></div>
                </div>
                <div class="col-12 col-md-3">
                    <div id="find"></div> 
                </div>
            </div>
        </div>
    </div>
    <div class="panel-body">
        <div id="txtSalesNo"></div>
        <div id="find"></div>
        <div class="demo-container" id="formresult">
            <div class='form-group row'>
                <label for='txtSaleNo' class='col-sm-4 control-label text-md-right'>Nomor Transaksi</label>
                <div class='col-sm-4'>
                    <input id='txtSaleNo' type='text' class='form-control' placeholder="Nomor Transaksi" readonly>
                </div>
            </div>
            <div class='form-group row'>
                <label for='txtSaleDate' class='col-sm-4 control-label text-md-right'>Tanggal Transaksi</label>
                <div class='col-sm-4'>
                    <input id='txtSaleDate' type='text' class='form-control' placeholder="Tanggal Transaksi" readonly>
                </div>
            </div>
            <table id='tableProd' class='table table-bordered table-striped'>
                <tr><th>PLU</th><th>Produk</th><th>Price</th><th>Qty</th><th>Total</th></tr>
                <tr><td></td><td></td><td></td><td></td><td></td></tr>
            </table>
        </div>
        <div id="cancel"></div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    $("#txtSalesNo").dxTextBox({
        label:{
            text:"Sales No"
        },
        placeholder: "Masukan Nomor Sales yang akan di batalkan...",
    });
    $("#find").dxButton({
        text: "Cari",
        type: "success",
        onClick: function() {
            var vsalesno=$("#txtSalesNo").dxTextBox("instance").option('value');
            if(vsalesno==""){
                        DevExpress.ui.notify({
                        message: "Masukan Nomor Penjualan yang akan dibatalkan",
                        position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 1000);
                    return ;
            };
            var url="{{URL::to('store/sales/getsalesbyno')}}"+"/"+vsalesno;
            $.getJSON( url, function( data ) {
                if(data.products.length!=0){
                    var result=" ";
                    result +="<div class='form-group row'>";
                    result +="<label for='txtSaleNo' class='col-sm-4 control-label text-md-right'>Nomor Transaksi</label>";
                    result +="<div class='col-sm-4'>";
                    result +="<input id='txtSaleNo' type='text' value="+data.sale_no+" class='form-control' readonly>"
                    result +="</div></div>"
                    result +="<div class='form-group row'>";
                    result +="<label for='txtSaleDate' class='col-sm-4 control-label text-md-right'>Tanggal Transaksi</label>";
                    result +="<div class='col-sm-4'>";
                    result +="<input id='txtSaleDate' type='text' value="+data.sale_date+" class='form-control' readonly>"
                    result +="</div></div>"                            
                    result +="<table id='tableProd' class='table table-bordered table-striped'>"
                    result +="<tr><th>PLU</th><th>Produk</th><th>Price</th><th>Qty</th><th>Total</th></tr>"
                    for(var i=0;i<data.products.length;i++)
                    {
                        result +="<tr>"
                        result += "<td>"+data.products[i].product_id+"</td>";
                        result += "<td>"+data.products[i].product_desc+"</td>";
                        result += "<td>"+data.products[i].saleproducts.sale_product_price+"</td>";
                        result += "<td>"+data.products[i].saleproducts.sale_product_qty+"</td>";
                        result += "<td>"+data.products[i].saleproducts.sale_product_total+"</td>";
                        result += "</tr>";
                    } 
                    result +="</table>";
                    $('#formresult').html(result);
                }else{
                    DevExpress.ui.notify({
                        message: "Transaksi dengan nomor "+vsalesno+" tidak ditemukan",
                        position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 1000);
                }
            }).fail(function(msg) {
                console.log(msg.responseText);
                if(msg.responseText=="BEDAHARI"){
                        DevExpress.ui.notify({
                        message: "Transaksi dengan nomor "+vsalesno+" hanya bisa dibatalkan pada hari yang sama",
                        position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 1000);
                    return ;
                };
                if(msg.responseText=="BATAL"){
                        DevExpress.ui.notify({
                        message: "Transaksi dengan nomor "+vsalesno+" sudah di batalkan",
                        position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 1000);
                    return ;
                };
            });
        },
    });
    $("#cancel").dxButton({
        text: "Batalkan Transaksi",
        type: "danger",
        onClick: function() {
            var vsalesno=$("#txtSalesNo").dxTextBox("instance").option('value');
            // var url="{{URL::to('sales/')}}"+"/"+vsalesno;
            var result = DevExpress.ui.dialog.confirm("Proses Pembatalan Transaksi dengan nomor "+vsalesno+" dilanjutkan ??", "Konfirmasi");
            result.done(function (dialogResult) {
                if(dialogResult){
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax(
                    {
                        url: "{{URL::to('store/sales/')}}"+"/"+vsalesno,
                        type: 'delete', 
                        dataType: "JSON",
                        data: {
                            // "id": vsalesno // method and token not needed in data
                        },
                        success: function (response)
                        {
                            console.log(response); // see the reponse sent
                        },
                        error: function(xhr) {
                        console.log(xhr.responseText); // this line will save you tons of hours while debugging
                        // do something here because of error
                    }
                    });
                }
                location.reload();
            });
        },
    });
});
</script>
@endsection