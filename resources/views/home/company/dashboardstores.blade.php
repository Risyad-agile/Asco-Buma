@extends('home.home_manager')

@section('manager')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
        <i class="fas fa-chart-pie mr-1"></i>
        <h3>Transaksi {!!$bulantahun!!}</h3>
    </div> 
    <div class="card-body">
        <div id="chartstoretime" ></div>
        <div id="chartstoresales" ></div>
    </div> 
</div>
@endsection

@section('managerscript')
  <script type="text/javascript">
  $(function(){
      var storesales={!!$storesales!!}  
      var storetimesales={!!$storetimesales!!}
      $("#chartstoretime").dxChart({
        //   dataSource:storetimesales,
          dataSource:storetimesales.sort(function (a, b) {
            return a.saletime - b.saletime;
            }),
          title: {
                 text: "Performa Penjualan Toko Harian",
                 subtitle: {
                     text: "({!!$bulantahun!!})"
                 }
            },
            palette: "blue",
        commonSeriesSettings: {
            argumentField: "saletime",
            valueField: "saletot",
            type: "area"
        },
        seriesTemplate: {
            nameField: "storename",
            customizeSeries: "storename",
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
            verticalAlignment: "bottom",
            horizontalAlignment: "center"
        }
      });
      $("#chartstoresales").dxChart({
        dataSource:storesales,
            title: {
                 text: "Performa Sales Bulanan",
                 subtitle: {
                     text: "({!!$bulantahun!!})"
                 }
            },
            palette: "violet",
            commonPaneSettings: {
              border: {
                  visible: true,
                  width: 2,
                  top: false,
                  right: false
              }
            },
            series: {
                argumentField: "storename",
                valueField: "saletot",
                type: "bar"
            },
            valueAxis: {
                title: {
                    text: "sales"
            },
            position: "right"
            },
          argumentAxis: {
            title: {
                text: "store"
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
            argumentField: "storename",
            ignoreEmptyPoints: true
          },
          seriesTemplate: {
            nameField: "storename",
          },
          tooltip: {
              format: "fixedPoint",
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


    $("#toolbar").dxToolbar({
        items: [{
                location: 'before',
                widget: 'dxButton',
                options: {
                    icon: 'chevronleft',
                    // text: 'Back',
                    onClick: function() {
                        DevExpress.ui.notify("Back button has been clicked!");
                    }
                }
            }, {
                location: 'center',
                locateInMenu: 'never',
                template: function() {
                    return $("<div class='toolbar-label'>Transaksi <b>{!!$bulantahun!!}</b></div>");
                }
            }, {
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "chevronnext",
                    onClick: function() {
                        DevExpress.ui.notify("Add button has been clicked!");
                    }
                }
            }
        ]
    });












  });

  </script>
@endsection