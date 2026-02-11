@extends('home.home_manager')

@section('manager')
{{-- <div class="long-title"><h3>Transaksi {!!$bulantahun!!}</div></h3></div> --}}
@if(count($dailytimesales)!=0)
<div class="card">
    <div class="card-body">
        <div id="chartdailytime" ></div>
    </div>
</div>
@endif
<div class="card">
    <div class="card-body">
        <div id="chartmonthly" ></div>
    </div>
    <div class="card-footer">
        <div class="row">
          <div class="col-sm-3 col-6">
            <div class="description-block border-right">
              @if($dashboard->dash_revenue_last===$dashboard->dash_revenue_month)
                <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> {!!number_format($dashboard->dash_revenue_last-$dashboard->dash_revenue_month)!!}</span>
              @elseif($dashboard->dash_revenue_last<$dashboard->dash_revenue_month)
                <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> {!!number_format($dashboard->dash_revenue_month-$dashboard->dash_revenue_last)!!}</span>
              @else
                <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> {!!number_format($dashboard->dash_revenue_last-$dashboard->dash_revenue_month)!!}</span> 
              @endif
              <h5 class="description-header">Rp. {!!number_format($dashboard->dash_revenue_month)!!}</h5>  
              <span class="description-text">TOTAL PENJUALAN</span>
            </div>
          </div>
          <div class="col-sm-3 col-6">
            <div class="description-block border-right">
              @if($dashboard->dash_cost_last===$dashboard->dash_cost_month)
                <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> {!!number_format($dashboard->dash_cost_last-$dashboard->dash_cost_month)!!}</span>
              @elseif($dashboard->dash_cost_last<$dashboard->dash_cost_month)
                <span class="description-percentage text-danger"><i class="fas fa-caret-up"></i> {!!number_format($dashboard->dash_cost_month-$dashboard->dash_cost_last)!!}</span>
              @else
                <span class="description-percentage text-success"><i class="fas fa-caret-down"></i> {!!number_format($dashboard->dash_cost_last-$dashboard->dash_cost_month)!!}</span> 
              @endif            <h5 class="description-header">Rp. {!!number_format($dashboard->dash_cost_month)!!}</h5>
              <span class="description-text">TOTAL BIAYA</span>
            </div>
          </div>
          <div class="col-sm-3 col-6">
            <div class="description-block border-right">
              @if($dashboard->dash_profit_last===$dashboard->dash_profit_month)
                <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> {!!number_format($dashboard->dash_profit_last-$dashboard->dash_profit_month)!!}</span>
              @elseif($dashboard->dash_profit_last<$dashboard->dash_profit_month)
                <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> {!!number_format($dashboard->dash_profit_month-$dashboard->dash_profit_last)!!}</span>
              @else
                <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> {!!number_format($dashboard->dash_profit_last-$dashboard->dash_profit_month)!!}</span> 
              @endif            <h5 class="description-header">Rp. {!!number_format($dashboard->dash_profit_month)!!}</h5>
              <span class="description-text">TOTAL PROFIT</span>
            </div>
          </div>
          <div class="col-sm-3 col-6">
            <div class="description-block">
              @if($dashboard->dash_cust_last===$dashboard->dash_cust_month)
                <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> {!!number_format($dashboard->dash_cust_last-$dashboard->dash_cust_month)!!}</span>
              @elseif($dashboard->dash_cust_last<$dashboard->dash_cust_month)
                <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> {!!number_format($dashboard->dash_cust_month-$dashboard->dash_cust_last)!!}</span>
              @else
                <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> {!!number_format($dashboard->dash_cust_last-$dashboard->dash_cust_month)!!}</span> 
              @endif            <h5 class="description-header">{!!number_format($dashboard->dash_cust_month)!!}</h5>
              <span class="description-text">TOTAL TAMU</span>
            </div>
          </div>
        </div>
      </div>
</div>
@endsection

@section('managerscript')
  <script type="text/javascript">
  $(function(){
      var daily={!!$dailytimesales!!}
      var monthly={!!$monthlysales!!}
      $("#chartdailytime").dxChart({
          dataSource:daily,
          title: {
                 text: "Performa Sales Harian",
                 subtitle: {
                     text: "(Bulan {{$bulantahun}})"
                 }
             },
          series: {
              label: {
                  visible: true,
                  backgroundColor: "#c18e92"
              },
              color: "#79cac4",
              type: "splinearea",
              argumentField: "saletime",
              valueField: "saletot",
          },
          valueAxis: {
            title: {
                text: "rupiah"
            },
            position: "right"
          },
          argumentAxis: {
            title: {
                text: "waktu"
            },
            position: "bottom"
          },
          "export": {
              enabled: true
          },
          legend: {
              visible: false
          }
      });
      $("#chartmonthly").dxChart({
          dataSource:monthly,
          palette: 'soft',
          title: {
                 text: "Penjualan Harian",
                 subtitle: {
                     text: "(Penjualan Selama Bulan {!!$bulantahun!!})"
                 }
             },
          series: 
        //   { 
        //       argumentField: "saleday",
        //       valueField: "saletot",
        //   },

          [{ valueField: "saletot", argumentField: "saleday", name: "Sales" },
            { valueField: "salemargin", argumentField: "saleday",name: "Profit" },
            ],
          valueAxis: {
            title: {
                text: "rupiah"
            },
            position: "right"
          },
          argumentAxis: {
            title: {
                text: "tanggal"
            },
            position: "bottom"
          },
          "export": {
              enabled: true
          },
          tooltip: {
                format: "fixedPoint",
              enabled: true,
              customizeTooltip: function (arg) {
                  return {
                      text: arg.seriesName+ " : "  + arg.valueText
                  };
              }
          },
          legend: {
              visible: false
          }
      });
      const formatNumber = new Intl.NumberFormat('en-US', { maximumFractionDigits: 0 }).format;
  });

  </script>
@endsection