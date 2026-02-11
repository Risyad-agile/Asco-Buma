<!-- //harga satuan produk pada saat beli
//harga pada saat di jual
selisih nya itu lah rugi laba 


Dalam satu bulan
Pendapatan (Revenue)            (+)
a. Penjualan
b. Discount
c. Retur
d. Pendapatan Lain

Harga Pokok Penjualan           (-)

Biaya -->

@extends('layouts.master')
@section('content')
    <div class="long-title"><h3>Laporan Laba Rugi Kotor {{$bulanlap}} Tahun {{$tahunlap}}</h3></div>
    <div class="col-md-8 col-md-offset-2">
        <div class="dx-field">
            <div class="dx-field-label">Bulan Transaksi</div>
            <div class="dx-field-value" >
                <div id="tglsales"></div>
            </div>
        </div>
      </div>
      {{-- <div class="dx-fieldset-header">.</div> --}}
      <div class="col-md-8 col-md-offset-2">
          <div class="panel panel-default">
          <div class="dx-field">
              <div class="dx-fieldset-header">A. Penjualan</div>
          </div>
          <div class="dx-field">
              <div class="dx-field-label">1. Penjualan</div>
              <div class="dx-field-value">
                  <div id="tot_product_sales"></div>
              </div>
          </div>
          <div class="dx-field">
              <div class="dx-field-label">2. Discount</div>
              <div class="dx-field-value">
                  <div id="tot_product_disc"></div>
              </div>
          </div>
          <div class="dx-field">
              <div class="dx-field-label">Sub Total (A1-A2)</div>
              <div class="dx-field-value">
                  <div id="tot_product_sales_disc"></div>
              </div>
          </div>
          <div class="dx-field">
              <div class="dx-fieldset-header">B. Harga Pokok Pembelian (HPP)</div>
          </div>
          <div class="dx-field">
              <div class="dx-field-label">1. HPP</div>
              <div class="dx-field-value">
                  <div id="tot_product_buy_price"></div>
              </div>
          </div>    
          <div class="dx-field">
              <div class="dx-fieldset-header">C. Rugi Laba Kotor {{$bulanlap}} {{$tahunlap}}</div>
          </div>
          <div class="dx-field">
              <div class="dx-field-label">P/L Gross (A1-A2)-(B1)</div>
              <div class="dx-field-value">
                  <div id="total_pl"></div>
              </div>
          </div>
          </div>
      </div>
@endsection

@section('script')
<script type="text/javascript">
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(function(){
    var date = new Date();
    var sales={!!$plgross!!}; 
    $("#tglsales").dxDateBox({
            placeholder: "Month: ",
            showClearButton: true,
            useMaskBehavior: true,
            displayFormat: "'Bulan': MM-yyyy",
            type: "date",
            value: date,
            onValueChanged: function(e){
                var tanggal=e.value;
                var dtgl=tanggal.getDate();
                var mtgl=tanggal.getMonth();
                var ytgl=tanggal.getFullYear();
                mtgl=mtgl+1;
                if(mtgl<10){
                    mtgl="0"+mtgl;
                }
                var  tgl=mtgl.toString()+ytgl.toString();
               
                var url="{{URL::to('agile/reports/plgross')}}"+"/"+tgl;
                $.ajax({
                    type: "GET",
                    url: url,
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function(response){
                        // window.location = url;
                        // $("#tot_prod_sales").dxNumberBox('instance').option('value',100000);
                        console.log(sales[0].tot_product_sales_price);
                    },
                    complete: function(jqXHR) {                    
                        window.location = url;
                        // $("#tot_prod_sales").dxNumberBox('instance').option('value',100000);
                    }
                });
                
            }
        });
    $("#tot_product_sales").dxNumberBox({
        format: "#,##0.##",
        disabled: true,
        value: sales[0].tot_product_sales_price,
        rtlEnabled: true,
    });
    $("#tot_product_disc").dxNumberBox({
        format: "#,##0.##",
        disabled: true,
        value: sales[0].tot_product_disc,
        rtlEnabled: true,
    });
    $("#tot_product_sales_disc").dxNumberBox({
        format: "#,##0.##",
        disabled: true,
        value: sales[0].tot_product_sales_disc,
        rtlEnabled: true,
    });
    $("#tot_product_buy_price").dxNumberBox({
        format: "#,##0.##",
        disabled: true,
        value: sales[0].tot_product_buy_price,
        rtlEnabled: true,
    });
    $("#total_pl").dxNumberBox({
        format: "#,##0.##",
        disabled: true,
        value: sales[0].total_pl,
        rtlEnabled: true,
    });

});
</script>
@endsection

