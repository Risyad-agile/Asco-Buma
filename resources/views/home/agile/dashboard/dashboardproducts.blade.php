@extends('home.home_manager')

@section('manager')
<!-- <div id="toolbar" style="padding-left: 200px;"></div> -->

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
        <i class="fas fa-chart-pie mr-1"></i>
        <h3>Transaksi Selama {!!$bulantahun!!}</h3>
    </div> 
    <div class="card-body">
        <div id="chartprodvalue"></div>
        <div id="chartprodprice"></div>
        <div id="chartprodcat"></div>
    </div> 
</div>


@endsection

@section('managerscript')
  <script type="text/javascript">
  $(function(){
      var prodvalue={!!$prodvaltopten!!} 
      var prodprice={!!$prodpricetopten!!} 
      var prodcat={!!$prodcategory!!}
      $("#chartprodvalue").dxChart({
            dataSource:prodvalue.sort(function (a, b) {
            return a.qtytot - b.qtytot;
            }),
            title: {
                 text: "Top Ten Product",
                 subtitle: {
                     text: "(Jumlah Penjualan)"
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
      $("#chartprodprice").dxChart({
        dataSource:prodprice.sort(function (a, b) {
            return a.saletot - b.saletot;
            }),
            title: {
                 text: "Top Revenue Product",
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
      $("#chartprodcat").dxPieChart({
        type: "doughnut",
        palette: "Soft Pastel",
        dataSource: prodcat,
        title: {
                 text: "Top Kategori Produk",
                 subtitle: {
                     text: "(Harga)"
                 }
            },
        size: {
            // height: 300,
            // width: 800,
        },    
        tooltip: {
            enabled: true,
            // format: "millions",
            customizeTooltip: function (arg) {
                // var percentText = Globalize.formatNumber(arg.percent, {  
                //     style: "percent",
                //     minimumFractionDigits: 2,
                //     maximumFractionDigits: 2
                // });
    
                return {
                    text: "Sales "+arg.argumentText+" : "+ arg.valueText,
                };
            }
        },
        legend: {
            horizontalAlignment: "right",
            verticalAlignment: "top",
            // margin: 0
        },
        "export": {
            enabled: true
        },
        series: [{        
            argumentField: "prodcat",
            valueField: "saletot",
            hoverStyle: {
                color: "#ffd700" 
            }
            // label: {
            //     visible: true,
            //     format: "millions",
            //     connector: {
            //         visible: true
            //     }
            // }
        }]
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