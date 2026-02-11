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
        margin-bottom: 5px;
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
    <h6 class="title">Kidswa Shop</h6>
    {{-- <label class="label">========================</label> --}}
    <table class="table table-bordered">
    {{$totdisc=0}}
    {{$totpurchase=0}}
      @foreach ($sales as $key => $sale)
        @if ($loop->first)
          <thead>
            <tr>
              <td>Inv:</td>
              <td>{{$sale->sale_no}}</td>
              <td>Tgl:</td>
              <td>{{date('d-m-Y',strtotime($sale->sale_date))}}</td>
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
            <td colspan="3">{{$sale->product_desc}}</td>
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
          <?php 
            if($totdisc!=0){
              echo("<tr><td colspan='2'>Hemat (Disc):</td>");
              echo("<td colspan='2' align='right'>");
              echo(number_format($totdisc));
              echo("</td></tr>");
            }
          
          ?>
          <tr>
            <td colspan="2">Total :</td>
            <td colspan="2" align="right">{{number_format($totpurchase-$totdisc)}}</td>
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
          <td colspan="2" align="right">{{number_format($salepay->sale_pay_payed-($totpurchase-$totdisc))}}</td>
      </tr>
      @endforeach
    </table>
    <label class="label">=================================</label>
    <label class="label">Terimakasih telah berbelanja</label>
    <label class="label">http://kidswa.web.id</label>
  </body>
</html>
