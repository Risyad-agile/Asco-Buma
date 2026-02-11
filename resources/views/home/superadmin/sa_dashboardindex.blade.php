@extends('home.agile.landing-manager')

@section('manager')
<div class="row ">
    <div class="col-md-12">
        <div class="panel panel-default">
          <div id="pivotgrid-chart"></div>
          <div id="pivotgrid"></div>
        </div>
    </div>
</div>
@endsection

@section('managerscript')
  <script type="text/javascript">
$(function() {

    var pivotGridChart = $("#pivotgrid-chart").dxChart({
        commonSeriesSettings: {
            type: "bar"
        },
        tooltip: {
            enabled: true,
            format: "fixedPoint",
            customizeTooltip: function(args) {
                return {
                    html: args.seriesName + " | Total<div class='currency'>" + args.valueText + "</div>"
                };
            }
        },
        size: {
            height: 200
        },
        adaptiveLayout: {
            width: 450
        }
    }).dxChart("instance");

    var pivotGrid = $("#pivotgrid").dxPivotGrid({
        allowSortingBySummary: true,
        allowFiltering: true,
        showBorders: true,
        showColumnGrandTotals: false,
        showRowGrandTotals: false,
        showRowTotals: false,
        showColumnTotals: false,
        fieldChooser: {
            enabled: true,
            height: 400
        },
        dataSource: {
            fields: [{
                caption: "Merchant",
                width: 120,
                dataField: "comp_brand",
                area: "row",
                sortBySummaryField: "Total"
            }, {
                caption: "Store",
                dataField: "store_name",
                width: 150,
                area: "row"
            }, {
                dataField: "sale_date",
                dataType: "date",
                area: "column"
            }, {
                groupName: "sale_date",
                groupInterval: "week",
                visible: false
            }, {
                caption: "Total",
                dataField: "sale_total",
                dataType: "number",
                summaryType: "sum",
                format: "fixedPoint",
                area: "data"
            }],
            store: sales
        }
    }).dxPivotGrid("instance");

    pivotGrid.bindChart(pivotGridChart, {
        dataFieldsDisplayMode: "splitPanes",
        alternateDataFields: false
    });

    function expand() {
        var dataSource = pivotGrid.getDataSource();
        // dataSource.expandHeaderItem("row", ["Alenka"]);
        dataSource.expandHeaderItem("column", [currentyear]);
    }

    setTimeout(expand, 0);
});

  </script>
@endsection