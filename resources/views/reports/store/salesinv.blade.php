<!DOCTYPE html>
<style media="screen">
      .table{
        font-family: 'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana;
        /* font-weight: 200; */
        font-size: 12px;
        /* text-align: center;
        margin-bottom: 20px; */
      }
      .title{
        font-family: 'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana;
        font-weight: 200;
        font-size: 20px;
        text-align: center;
        /* margin-bottom: 1px; */
      }
      .title2{
        font-family: 'Segoe UI Light', 'Helvetica Neue Light', 'Segoe UI', 'Helvetica Neue', 'Trebuchet MS', Verdana;
        font-weight: 100;
        font-size: 10px;
        text-align: center;
        margin-bottom: 2px;
      }
      .label{
        font-size: 10px;
      }
</style>

<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <h3 class="title">{{$store->store_name}}</h3>
    {{-- <h6 class="title2">{{$store->store_address}}</h6> --}}
    <table class="table table-bordered">
    {{$totdisc=0}}
    {{$totpurchase=0}}
    {{$admfee=0}}
  
      @foreach ($sales as $key => $sale)
        @if ($loop->first)
          <thead>
            <tr>
              <td>No:</td>
              <td>{{$sale->sale_no}}</td>
              <td>Tgl:</td>
              <td>{{date('d-m-y',strtotime($sale->sale_date))}}</td>
            </tr>
            <tr>
              <td colspan="4">
                <label class="label">-------------------------------------------------------</label>
              </td>
            </tr>
            <tr>
              <th>No</th>
              <th>Produk</th>
              <th>Jml</th>
              <th>Hrg</th>
            </tr>
            <tr>
              <td colspan="4">
                <label class="label">-------------------------------------------------------</label>
              </td>
            </tr>
          </thead>
        @endif
        <tbody>
          <tr>
            <td>{{$loop->iteration}}</td>
            <td colspan="3">{{$sale->product_name}}
              <?php
                if($sale->product_stock_state=='0'){
                  echo(" (");
                  echo($sale->nonstock_reff);
                  echo(")");
                  $admfee=$admfee+$sale->nonstock_fee;
                }
              ?>
            </td>
          </tr>
          <tr>
            <td></td>
            <td>{{$sale->sale_product_qty}} x </td>
            <td>{{number_format($sale->sale_product_price)}}</td>
            <td align="right">{{number_format(($sale->sale_product_price*$sale->sale_product_qty))}}</td>
          </tr>
          <?php 
          if ($sale->sale_product_disc!=0) {
              echo("<tr><td></td><td></td>");
              echo("<td>Disc :</td>");
              echo("<td align='right'>");
              echo(number_format($sale->sale_product_disc));
              echo("</td></tr>");
            } ?>
          
            {{$totdisc=$totdisc+$sale->sale_product_disc}}
            {{$totpurchase=$totpurchase+($sale->sale_product_price*$sale->sale_product_qty)}}
        </tbody>
          @if ($loop->last)
          @endif
      @endforeach
      <tfoot>
          <tr>
            <td colspan="4">
              <label class="label">-------------------------------------------------------</label>
            </td>
          </tr>
          <tr>
            <td colspan="2">Sub Total :</td>
            <td colspan="2" align="right">{{number_format($totpurchase)}}</td>
          </tr>
          {{$totpurchase=$totpurchase+$admfee}}
          <?php 
          if($admfee!=0){
            echo("<tr><td colspan='2'>Biaya Admin :</td>");
            echo("<td colspan='2' align='right'>");
            echo(number_format($admfee));
            echo("</td></tr>");
          }
        ?>
          <?php 
            if($sale->sale_disc!=0){
              echo("<tr><td colspan='2'>Hemat (Disc):</td>");
              echo("<td colspan='2' align='right'>");
              echo(number_format($sale->sale_disc));
              echo("</td></tr>");
            }
          
          ?>
          <tr>
            <td colspan="2">Total :</td>
            <td colspan="2" align="right">{{number_format($totpurchase-$sale->sale_disc)}}</td>
          </tr>
      </tfoot>
      @foreach ($salespay as $key => $salepay)
      <!-- <tr>
          <td colspan="4">
            <label class="label">-------------------------------------------------------</label>
          </td>
      </tr> -->
      <tr>
          <td colspan="2">Bayar :</td>
          <td colspan="2" align="right">{{number_format($salepay->sale_pay_payed)}}</td>
      </tr>
      <tr>
          <td colspan="2">Kembali :</td>
          <td colspan="2" align="right">{{number_format($salepay->sale_pay_payed-($totpurchase-$sale->sale_disc))}}</td>
      </tr>
      @endforeach
    </table>
    <label class="label">=================================</label>
    <label class="label">Terimakasih telah berbelanja</label>
    <label class="label">powered by : http://kidswa.web.id</label>
    {{-- <label class="label"><p>Power By : http://zaida.online</label>  --}}
  </body>
</html>
