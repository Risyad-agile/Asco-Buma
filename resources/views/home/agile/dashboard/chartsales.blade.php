@extends('home.home_manager')

@section('manager')
<div class="long-title"><h3>Transaksi {!!$bulantahun!!}</div></h3></div>
<div class="row">
    <div class="card col-md-5">
        <div class="card-body">
            <div class="row">
                <div id="chartdailytime" ></div>
            </div>
        </div>
    </div>
    <div class="card col-md-5">
        <div class="card-body">
            <div class="row">
            <div id="chartmonthly" ></div>
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
      bulan = new Array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'Nopember', 'Desember');
      bulanini=bulan[new Date().getMonth()];
      $("#chartdailytime").dxChart({
          dataSource:daily,
          title: {
                 text: "Performa Sales Harian",
                 subtitle: {
                     text: "(Tanggal)"
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
          title: {
                 text: "Penjualan Harian",
                 subtitle: {
                     text: "(Penjualan Selama Bulan "+bulanini+")"
                 }
             },
          series: {
              color: "#79cac4",
              type: "spline",
              argumentField: "saleday",
              valueField: "saletot",
              label :{
                format: "fixedPoint",
              }
          },
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
              enabled: true,
              customizeTooltip: function (arg) {
                  return {
                      text: "Sales : " + arg.valueText
                  };
              }
          },
          legend: {
              visible: false
          }
      });
  });

  </script>
@endsection