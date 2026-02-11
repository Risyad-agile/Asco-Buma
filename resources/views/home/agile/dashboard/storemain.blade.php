@extends('layouts.master')
@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-8">
        <h1 class="m-0 text-dark"><span class="brand-text font-weight-light">Dashboard {!!$store->store_name!!}</span></h1>
        @if($dashboard->dash_pending_sales!=null)
          <h4 class="blinking"><b>Live...</b></h4>
        @endif
      </div> 
    </div> 
 
    @if($dashboard->dash_pending_sales!=null)
    <div class="row">
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-green"><i class="fas fa-money-check-alt"></i></span>

          <div class="info-box-content">
            <span class="info-box-text">Pesanan</span>
            <span class="info-box-number"><small>Rp.</small>{!!number_format($dashboard->dash_pending_sales)!!}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-fuchsia"><i class="ion ion-ios-people-outline"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Tamu</span>
            <span class="info-box-number">{!!number_format($dashboard->dash_pending_qty)!!}</span>
          </div>
        </div>
      </div>
  
      <!-- fix for small devices only -->
      <div class="clearfix visible-sm-block"></div>
  
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-maroon"><i class="fas fa-microchip"></i></span>
  
          <div class="info-box-content">
            <span class="info-box-text">Meja Tersedia</span>
            <span class="info-box-number">{!!number_format($dashboard->dash_spot_avail)!!} dari {!!number_format($dashboard->dash_spot_total)!!}</</span>
          </div>
          <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
      </div>
      <!-- /.col -->
      <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
          <span class="info-box-icon bg-blue"><i class="fas fa-file-invoice-dollar"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Perkiraan Transaksi</span>
            <span class="info-box-number"><small>Rp.</small>{!!number_format($dashboard->dash_sales_value)!!}</span>
          </div>
        </div>
      </div>
    </div>
    @endif
  
    <div class="card">
      <div class="card-header">
          <h3 class="card-title">
          <i class="fas fa-chart-line mr-1"></i>
          Product Trend Hari Ini
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <div class="btn-group">
              <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-wrench"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-right" role="menu">
                <a href="#" class="dropdown-item">Action</a>
                <a href="#" class="dropdown-item">Another action</a>
                <a href="#" class="dropdown-item">Something else here</a>
                <a class="dropdown-divider"></a>
                <a href="#" class="dropdown-item">Separated link</a>
              </div>
            </div>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
      </div><!-- /.card-header -->
      <div class="card-body">
        <div class="row">
        <div class="col-md-6">
          <div id="chartprodprice"></div>
        </div>
        <div class="col-md-6">
          <div id="chartprodvalue"></div>
        </div>
      </div> 
      </div>

      <!-- /.card-footer -->
  </div>

    <!-- Custom tabs (Charts with tabs)-->
  <div class="card">
      <div class="card-header">
          <h3 class="card-title">
          <i class="fas fa-chart-area mr-1"></i>
          Total Sales Bulan {!!$bulantahun!!}
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
            <div class="btn-group">
              <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-wrench"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-right" role="menu">
                <a href="#" class="dropdown-item">Action</a>
                <a href="#" class="dropdown-item">Another action</a>
                <a href="#" class="dropdown-item">Something else here</a>
                <a class="dropdown-divider"></a>
                <a href="#" class="dropdown-item">Separated link</a>
              </div>
            </div>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
      </div><!-- /.card-header -->
      <div class="card-body">
          <div class="tab-content p-0">
          <div class="chart tab-pane active" id="chartmonthlysales_area" style="position: relative; height: 300px;"></div>  
          {{-- <div class="chart tab-pane" id="chartmonthlysales_bar" style="position: relative; height: 300px;"></div> --}}
          </div>
      </div> 
      <div class="card-footer">
        <div class="row">
          <div class="col-sm-3 col-6">
            <div class="description-block border-right">
              {{-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 17%</span> --}}
              <h5 class="description-header">Rp. {!!number_format($dashboard->dash_revenue_month)!!}</h5>
              <span class="description-text">TOTAL REVENUE</span>
            </div>
            <!-- /.description-block -->
          </div>
          <!-- /.col -->
          <div class="col-sm-3 col-6">
            <div class="description-block border-right">
              {{-- <span class="description-percentage text-warning"><i class="fas fa-caret-left"></i> 0%</span> --}}
              <h5 class="description-header">Rp. {!!number_format($dashboard->dash_cost_month)!!}</h5>
              <span class="description-text">TOTAL BIAYA</span>
            </div>
            <!-- /.description-block -->
          </div>
          <!-- /.col -->
          <div class="col-sm-3 col-6">
            <div class="description-block border-right">
              {{-- <span class="description-percentage text-success"><i class="fas fa-caret-up"></i> 20%</span> --}}
              <h5 class="description-header">Rp. {!!number_format($dashboard->dash_profit_month)!!}</h5>
              <span class="description-text">TOTAL PROFIT</span>
            </div>
            <!-- /.description-block -->
          </div>
          <!-- /.col -->
          <div class="col-sm-3 col-6">
            <div class="description-block">
              {{-- <span class="description-percentage text-danger"><i class="fas fa-caret-down"></i> 18%</span> --}}
              <h5 class="description-header">{!!number_format($dashboard->dash_cust_month)!!}</h5>
              <span class="description-text">TAMU</span>
            </div>
            <!-- /.description-block -->
          </div>
        </div>
        <!-- /.row -->
      </div>
      <!-- /.card-footer -->
  </div>
 
  </div> 
</div>


@endsection

@section('script')
  <script type="text/javascript">
  $(function(){
      var monthlysales={!!$dashboard->dash_sales_cart_monthly!!};     
 
      var chart = $("#chartmonthlysales_area").dxChart({
        // palette: "Harmony Light",
        title: {
                 text: "Penjualan Harian {!!$store->store_name!!}",
                 subtitle: {
                     text: "(Penjualan Selama Bulan {!!$bulantahun!!})"
                 }
            },
        dataSource: monthlysales,
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

    var prodvalue={!!$dashboard->dash_chart_prodvaltopten!!}; 
    var prodprice={!!$dashboard->dash_chart_prodpricetopten!!}; 

    $("#chartprodvalue").dxChart({
        dataSource:prodvalue.sort(function (a, b) {
            return a.qtytot - b.qtytot;
        }),
        title: {
              text: "Berdasarkan Jumlah",
              subtitle: {
                  text: "(Jumlah Jual)"
              }
        },
        commonPaneSettings: {
          border: {
              visible: true,
              width: 2,
              top: false,
              right: false
          }
        },
        series: {
            argumentField: "prodname",
            valueField: "qtytot",
            type: "spline"
        },
        valueAxis: {
            title: {
                text: "quantity"
            },
            position: "right"
        },
        argumentAxis: {
          title: {
              text: "product"
          },
          position: "bottom",
          label: {
              // wordWrap: "normal",
              // overlappingBehavior: "rotate",
              visible:false,
          }
        },
        tooltip: {
            enabled: true,
            customizeTooltip: function (arg) {
                return {
                  text: arg.argumentText +(" ("+arg.valueText+")")
                };
            }
        },
        "export": {
            enabled: true
        },
        legend: {
            visible: false
        }
      });
      $("#chartprodprice").dxChart({
        dataSource:prodprice.sort(function (a, b) {
            return a.saletot - b.saletot;
            }),
            title: {
                 text: "Berdasarkan Harga",
                 subtitle: {
                     text: "(Harga Jual)"
                 }
            },
            palette: "soft",
            commonPaneSettings: {
              border: {
                  visible: true,
                  width: 2,
                  top: false,
                  right: false
              }
            },
            series: {
                argumentField: "prodname",
                valueField: "saletot",
                type: "bar"
            },
            valueAxis: {
                title: {
                    text: "price"
            },
            position: "right"
            },
          argumentAxis: {
            title: {
                text: "product"
            },
            position: "bottom",
            label: {
                // wordWrap: "normal",
                // overlappingBehavior: "rotate",
                visible:false,
            }
          },
          commonSeriesSettings: {
            type: "bar",
            valueField: "saletot",
            argumentField: "prodname",
            ignoreEmptyPoints: true
          },
          seriesTemplate: {
            nameField: "prodname",
          },
          tooltip: {
              enabled: true,
              customizeTooltip: function (arg) {
                  return {
                    text: arg.argumentText +(" ("+arg.valueText+")")
                  };
              }
          },
          "export": {
              enabled: true
          },
          legend: {
              visible: false
          }
      });
  });

  </script>
@endsection