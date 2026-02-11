@extends('home.home_manager')

@section('manager')
<div class="row">
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-info">
        <div class="inner">
          <div class="long-title"><h4>Rp. {!!number_format($dashboard->dash_revenue)!!}</h4></div>
            <p>Penjualan Harian</p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
        <a href="{{route('office.reports.dailysales.index')}}" class="small-box-footer">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-success">
        <div class="inner">
          <div class="long-title"><h4>Rp. {!!number_format($dashboard->dash_revenue_month)!!}</h4></div>
            <p>Penjualan Bulanan</p>
          </div>
          <div class="icon">
            <i class="ion ion-stats-bars"></i>
          </div>
        <a href="{{route('office.reports.dailysales.index')}}" class="small-box-footer">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-warning">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_total_store)!!}</h4></div>
            <p>Total Stores</p>
          </div>
          <div class="icon">
            <i class="ion ion-bag"></i>
          </div>
        <a href="#" class="small-box-footer">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-6">
      <!-- small box -->
      <div class="small-box bg-danger">
        <div class="inner">
          <div class="long-title"><h4>{!!number_format($dashboard->dash_not_active_store)!!}</h4></div>
            <p>Store Tidak Aktif (>3 Hari)</p>
          </div>
          <div class="icon">
            <i class="fas fa-store"></i>
          </div>
        <a href="#" class="small-box-footer">Selengkapnya <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <!-- ./col -->
  </div>
  <!-- /.row -->
<!-- Custom tabs (Charts with tabs)-->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
        <i class="fas fa-chart-bar mr-1"></i>
        Total Sales Bulan {!!$bulantahun!!}
        </h3>
        <div class="card-tools">
        </div>
    </div><!-- /.card-header -->
    <div class="card-body">
        <div class="tab-content p-0">
        <!-- Morris chart - Sales -->
        <div class="chart tab-pane active" id="chartmonthlysales_bar" style="position: relative; height: 300px;"> 
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
            <span class="description-text">TOTAL REVENUE</span>
          </div>
          <!-- /.description-block -->
        </div>
        <!-- /.col -->
        <div class="col-sm-3 col-6">
          <div class="description-block border-right">
            @if($dashboard->dash_cost_last===$dashboard->dash_cost_month)
              <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> {!!number_format($dashboard->dash_cost_last-$dashboard->dash_cost_month)!!}</span>
            @elseif($dashboard->dash_cost_last<$dashboard->dash_cost_month)
              <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> {!!number_format($dashboard->dash_cost_month-$dashboard->dash_cost_last)!!}</span>
            @else
              <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> {!!number_format($dashboard->dash_cost_last-$dashboard->dash_cost_month)!!}</span> 
            @endif            <h5 class="description-header">Rp. {!!number_format($dashboard->dash_cost_month)!!}</h5>
            <span class="description-text">TOTAL BIAYA</span>
          </div>
          <!-- /.description-block -->
        </div>
        <!-- /.col -->
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
          <!-- /.description-block -->
        </div>
        <!-- /.col -->
        <div class="col-sm-3 col-6">
          <div class="description-block">
            @if($dashboard->dash_cust_last===$dashboard->dash_cust_month)
              <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> {!!number_format($dashboard->dash_cust_last-$dashboard->dash_cust_month)!!}</span>
            @elseif($dashboard->dash_cust_last<$dashboard->dash_cust_month)
              <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> {!!number_format($dashboard->dash_cust_month-$dashboard->dash_cust_last)!!}</span>
            @else
              <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> {!!number_format($dashboard->dash_cust_last-$dashboard->dash_cust_month)!!}</span> 
            @endif            <h5 class="description-header">{!!number_format($dashboard->dash_cust_month)!!}</h5>
            <span class="description-text">TRANSAKSI</span>
          </div>
          <!-- /.description-block -->
        </div>
      </div>
      <!-- /.row -->
    </div>
    <!-- /.card-footer -->
</div>
<!-- /.card -->
<!-- Custom tabs (Charts with tabs)-->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
        <i class="fas fa-chart-bar mr-1"></i>
        Peta Penjualan Merchant
        </h3>
        <div class="card-tools">
        </div>
    </div><!-- /.card-header -->
    <div class="card-body">
        <div class="tab-content p-0">
        <!-- Morris chart - Sales -->
        <div class="chart tab-pane active" id="salesall" style="position: relative; height: 300px;"> 
        </div>

        </div>
    </div> 
    <div class="card-footer">
    </div>
    <!-- /.card-footer -->
</div>
<!-- /.card --> 
@endsection

@section('managerscript')
  <script type="text/javascript">
  $(function(){
      var monthlysales={!!$dashboard->dash_sales_cart_monthly!!};   
      var allsales={!!$dashboard->dash_sales_all!!};  
      $("#chartmonthlysales_bar").dxChart({
          dataSource:monthlysales,
        //   palette: "soft",
          title: {
                 text: "Penjualan Harian Semua Merchant",
                 subtitle: {
                     text: "(Penjualan Selama Bulan {!!$bulantahun!!})"
                 }
            },
          commonSeriesSettings: {
                // barPadding: 0.5,
                ignoreEmptyPoints: true,
                argumentField: "saleday",
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
                text: "tanggal"
            },
            position: "bottom"
          },
          tooltip: {
              enabled: true,
              format: "fixedPoint",
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
      $("#salesall").dxPivotGrid({
        allowSortingBySummary: true,
        allowSorting: true,
        allowFiltering: true,
        allowExpandAll: true,
        height: 440,
        showBorders: true,
        fieldChooser: {
            enabled: false
        },
        export: {
            enabled: true,
            fileName: "Sales"
        },
        dataSource: {
            fields: [{
                caption: "Merchant",
                width: 120,
                dataField: "comp_brand",
                area: "row" 
            }, {
                caption: "Store",
                dataField: "store_name",
                width: 150,
                area: "row",
                selector: function(data) {
                    return  data.store_name + " (" + data.comp_brand + ")";
                }
            }, {
                dataField: "sale_date",
                // dataType: "string",
                area: "column"
            }, {
                caption: "Sales",
                dataField: "sales_count",
                dataType: "number",
                summaryType: "count",
                format: "fixedPoint",
                area: "data"
            }],
            store: allsales
        }
    });
  });

  </script>
@endsection