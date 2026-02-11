@extends('layouts.master')
@section('content')
<div class="content">
    <div class="long-title"><h3>Daftar Menu Resto</h3></div>
    <div id="gridProduct"></div>
</div>

@endsection

@section('script')
  <script type="text/javascript">
  $(function() {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var promos={!!$promos!!}
    $("#gridProduct").dxDataGrid({
        dataSource: promos,
        keyExpr: "promo_no",
        showBorders: true,
        columns: [{
                dataField: "promo_no",
                caption: "Product ID",
                width: 150,
            },{
                dataField: "promo_rule",
                caption:"Kriteria",
                width: 150,
            },{
                dataField: "promo_desc",
                caption:"Product Desc",
                // width: 170
            },{
                dataField: "promo_price",
                caption: "Harga",
                dataType:"number",
                format:"fixedPoint",
            }
        ],
        masterDetail: {
            enabled: true,
            template: function(container, options) { 
                var promoData = options.data;

                $("<div>")
                    .addClass("master-detail-caption")
                    .text("Rincian Menu : "+promoData.promo_no + " " + promoData.promo_desc)
                    .appendTo(container);

                $("<div>")
                    .dxDataGrid({
                        columnAutoWidth: true,
                        showBorders: true,
                        columns: [{
                            dataField: "product_id",
                            caption:"Product ID",
                        },{
                            dataField: "product_desc",
                            caption: "Produk"
                        },{
                            dataField:"promotionproducts.promo_product_qty",
                            caption:"Jumlah",
                            dataType:"number",
                        },{
                            dataField:"promotionproducts.promo_product_price",
                            caption: "Harga",
                            dataType: "number",
                            format: "fixedPoint",
                            // calculateCellValue: function(rowData) {
                            //     return rowData.Status == "Completed";
                            // }
                        }],
                        dataSource: promoData.products,
                    }).appendTo(container);
            }
        }
    });

  });
  </script>
@endsection
