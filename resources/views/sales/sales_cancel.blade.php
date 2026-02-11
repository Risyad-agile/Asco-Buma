@extends('layouts.master')
@section('content')

<div class="form">
    <div class="card">
        <div class="card-body">
            <label>Masukan Nomor Sales (SALE-NO)</label>
            <div class='form-group row'>
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
        <div class="form" id="formresult">
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
            <div id="tabSales"></div>
        </div>
        <div id="cancel"></div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#txtSalesNo").dxTextBox({
        placeholder: "Masukan Nomor Sales yang akan di batalkan...",
    });
    var dataSales=[];
    $("#tabSales").dxDataGrid({
        dataSource: dataSales,
        height: 250,
        type:"array",
        rowAlternationEnabled: true,
        showBorders: true,
        scrolling: {
          mode: "virtual"
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
                dataField: "sale_products.sale_product_price",
                caption: "Harga",
                dataType: "number",
                format: "fixedPoint",
                width: 75,
            },{
                dataField: "sale_products.sale_product_qty",
                caption: "Jumlah",
                dataType: "number",
                width: 75,
            },{
                dataField: "sale_products.sale_product_total",
                caption: "Total",
                dataType: "number",
                format: "fixedPoint",
                width: 100,
            },
        ],
        summary: {
          totalItems: [{
              column: "product_plu",
              summaryType: "count",
              displayFormat: "Items {0}",
          },{
              column: "sale_products.sale_product_total",
              summaryType: "sum",
              dataType:"number",
              valueFormat: "fixedPoint",
              displayFormat: "Rp. {0}",
          }]},
    });
    const storeid='{!!$store->id!!}';
    var saleid=0;
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

            $.ajax({
              type: "GET",
              url: "{{URL::to('farma/sales/cancel/store')}}"+"/"+storeid+"/number/"+vsalesno,
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              success: function (data) {
                if(data.sale_no){ 
                    saleid=data.id; 
                    dataSales=data.products;
                    $("#txtSaleNo").val(data.sale_no); 
                    $("#txtSaleDate").val(data.sale_date); 
                    $("#tabSales").dxDataGrid("instance").option("dataSource", dataSales);  
                }else{
                    if(data.code != 200) {
                        Swal.fire({
                            icon: data.status,
                            title: "Validation Error",
                            text: data.message,
                        })
                    }
                }
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

        },
    });
    $("#cancel").dxButton({
        text: "Batalkan Transaksi",
        type: "danger",
        onClick: function() {
            var vsalesno=$("#txtSalesNo").dxTextBox("instance").option('value');
            // var url="{{URL::to('sales/')}}"+"/"+vsalesno;
            Swal.fire({
                title: 'Konfirmasi',
                text: "Proses Pembatalan Transaksi dengan nomor "+vsalesno+" dilanjutkan ??",
                type: 'warning',
                showCancelButton: true,
                // confirmButtonColor: '#3085d6',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Batalkan!!',
                cancelButtonText: 'Tidak Jadi',
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: "DELETE",
                        url: "{{URL::to('farma/sales/')}}"+"/"+saleid,
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        success: function (data) {
                            if(data.code != 200) {
                                Swal.fire({
                                    icon: data.status,
                                    title: "Validation Error",
                                    text: data.message,
                                    footer: '<a href="">Why do I have this issue?</a>'
                                })
                            }else{
                                Swal.fire({
                                    icon: data.status,
                                    title: "Hapus Transaksi",
                                    text: data.message,
                                }).then((result) => {
                                    window.location.href = '{{route('home')}}';
                                });
                            }
                            return false;
                        },    
                        error: function(data) {
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
                }
            })


        },
    });
});
</script>
@endsection