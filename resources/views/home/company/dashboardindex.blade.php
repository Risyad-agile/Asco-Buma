@extends('home.home_manager')

@section('manager')
<div class="row">
  <div class="card">
  <div class="col-md-12">
      <div class="d-flex justify-content-between align-items-center breaking-news bg-white">
          <div class="d-flex flex-row flex-grow-1 flex-fill justify-content-center bg-danger py-2 text-white px-1 news"><span class="d-flex align-items-center"><i class="fas fa-envelope"></i></span></div>
          <marquee class="news-scroll" behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();"> 
            <a href="#">Agile Sustainability Report and Information </a> <span class="dot"></span> 
            <a href="#"> [ASRI] </a> <span class="dot"></span> 
            <a href="#">for futher information please visit https://agile.co.id</a> </marquee>
      </div>
  </div>
  </div>
</div>

<div class="row">
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-info">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_account_styles)!!}</h4></div>
            <p>Account Styles</p>
          </div>
          <div class="icon">
            <i class="fa fa-cubes"></i>
          </div>
        <a href="{{route('accountstyle.index')}}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_locations)!!}</h4></div>
            <p>Locations</p>
          </div>
          <div class="icon">
            <i class="fas fa-map-marker-alt"></i>
          </div>
        <a href="{{route('location.index')}}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-warning">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_process_total)!!}</h4></div>
            <p>Receive Data</p>
          </div> 
          <div class="icon">
            <i class="far fa-folder-open"></i>
          </div>
        <a href="{{route('processdatalog.index')}}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-danger">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format((float)$dashboard->dash_process_error, 2, '.', '')!!}<sup style="font-size: 20px">%</sup></h4></div>
            <p>Error Process</p>
          </div>
          <div class="icon">
            <i class="fas fa-exclamation-circle"></i>
          </div>
        <a href="{{route('processdatalog.error.index')}}" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
  </div>
  <!-- /.row -->


{{-- <div class="card">
    <div class="card-header">
        <h3 class="card-title">
        <i class="fas fa-chart-bar mr-1"></i>
        Total Migration Year{!!$activeyear!!}
        </h3>
        <div class="card-tools">
        <ul class="nav nav-pills ml-auto">
            <li class="nav-item">
              <a class="nav-link" href="{{route('dashboard.manager.sales')}}">Progress</a>
            </li>
        </ul>
        </div>
    </div> 
    <div class="card-body">
        <div class="tab-content p-0">
        <div class="chart tab-pane active" id="chartyearlysales_bar" style="position: relative; height: 300px;">
        </div>
        </div>
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
</div> --}}
 
@endsection

@section('managerscript')
<script type="text/javascript">
  $(function(){
      var yearlysales={!!$dashboard->dash_sales_chart_yearly!!};     
      $("#chartyearlysales_bar").dxChart({
          dataSource:yearlysales,
        //   palette: "soft",
          title: {
                 text: "Penjualan Bulanan Semua Toko",
                 subtitle: {
                     text: "(Penjualan Selama {!!$activeyear!!})"
                 }
            },
          commonSeriesSettings: {
                // barPadding: 0.5,
                ignoreEmptyPoints: true,
                argumentField: "salemonth",
                type: "stackedBar"
            },
          series: [
            { valueField: "saletot", name: "Sales" },
            { valueField: "salemargin", name: "Margin" },
          ],
          legend: {
            verticalAlignment: "bottom",
            horizontalAlignment: "center",
            itemTextPosition: 'top'
          },
          valueAxis: {
            title: {
                text: "juta rupiah"
            },
            position: "right"
          },
          argumentAxis: {
            title: {
                text: "bulan"
            },
            position: "bottom"
          },
          tooltip: {
              format: "fixedPoint",
              enabled: true,
              customizeTooltip: function (arg) {
                  return {
                      text: arg.seriesName + ": " + arg.valueText
                  };
              }
          },
          "export": {
              enabled: true
          },
          legend: {
              visible: true
          }
      });
      var chart = $("#chartyearlysales_area").dxChart({
        // palette: "Harmony Light",
        title: {
                 text: "Penjualan Harian Semua Toko",
                 subtitle: {
                     text: "(Penjualan Selama Bulan {!!$activeyear!!})"
                 }
            },
        dataSource: yearlysales,
        commonSeriesSettings: {
            type: "area",
            argumentField: "saleday"
        },
        series: [
          { valueField: "saletot", name: "Sales" },
          { valueField: "salemargin", name: "Margin" },
        ],
        // margin: {
        //     bottom: 20
        // },
        valueAxis: {
            title: {
                text: "juta rupiah"
            },
            position: "right"
          },
        argumentAxis: {
            // valueMarginsEnabled: false,
            title: {
                text: "tanggal"
            },
            position: "bottom"
        },
        "export": {
            enabled: true
        },
        tooltip: {
              enabled: true,
              customizeTooltip: function (arg) {
                  return {
                      text: arg.seriesName + ": " + arg.valueText
                  };
              }
          },
        legend: {
          verticalAlignment: "bottom",
            horizontalAlignment: "center",
            itemTextPosition: 'top'
        }
    }).dxChart("instance");
  });

  </script>
@endsection