@extends('layouts.master')
@section('content')
<div class="content">
  <body class="dx-viewport">
      <div class="long-title"><h3>Daftar Promo</h3></div>
      <form id="form-container" class="first-group">
          <div id="form"></div>
          <div class="second-group">
              <div id="gridContainer"></div>
          </div>
          @foreach ($promos as $key => $promo)
            {{-- <input type="hidden" value="{{$promo->locations->loc_name}}"> --}}
            @foreach ($promo->products as $keys => $prod)
            @endforeach
          @endforeach
      </form>
  </body>
</div>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
      var promos = {!! $promos !!};
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

      $("#gridContainer").dxDataGrid({
          dataSource: promos,
          keyExpr: "promo_no",
          showBorders: true,
          allowColumnResizing: true,
          searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
          paging: {
              pageSize: 7
          },
          columns: [{
                  dataField: "promo_no",
                  caption: "Kode Promo",
              },{
                  dataField: "promo_desc",
                  caption: "Nama Promo",
              },{
                  dataField: "promo_date_start",
                  caption: "Tanggal Mulai",
                  dataType: "date",
                  format:'dd-MM-yyyy',
              },{
                  dataField: "promo_date_end",
                  caption: "Tanggal Akhir",
                  dataType: "date",
                  format:'dd-MM-yyyy',
              },{
                  dataField: "promo_rule",
                  caption: "Ketentuan",
              },{
                  dataField: "promo_type",
                  caption: "Tipe Promo",
                  calculateCellValue: function(rowData) {
                    var status="Free";
                    if(rowData.promo_type!='2'){
                      status="Discount";
                    }
                    return status;
                  }
              },{
                  dataField: "promo_state",
                  caption: "Status Aktif",
                  calculateCellValue: function(rowData) {
                    var status="Aktif";
                    if(rowData.promo_state=='0'){
                      status="Tidak Aktif";
                    }
                    return status;
                  }
              },
          ],
          masterDetail: {
            enabled: true,
            template: function(container, options) {
                var roProductData = options.data;
                $("<div>")
                    .addClass("master-detail-caption")
                    .text("Promo : "+roProductData.promo_desc )
                    .appendTo(container);
                $("<div>")
                    .dxDataGrid({
                        columnAutoWidth: true,
                        showBorders: true,
                        columns: [
                        {
                            dataField: "product_id",
                            caption: "PLU",
                        },{
                            dataField: "product_desc",
                            caption: "Deskripsi",
                        },{
                            dataField: "promotionproducts.promo_product_qty",
                            caption: "Jumlah",
                        // },{
                        //     dataField: "product_price",
                        //     caption: "Harga Produk",
                        //     dataType: "number",
                        //     format: "fixedPoint",
                        },{
                            dataField: "selisih",
                            caption: "Discount",
                            dataType: "number",
                            format: "fixedPoint",
                            calculateCellValue: function(rowData) {
                              var selisih=rowData.product_price-rowData.promotionproducts.promo_product_price;
                              return selisih;
                            }
                        }],
                        summary: {
                            totalItems: [{
                                column: "product_id",
                                summaryType: "count",
                                displayFormat: "Jumlah Items {0}",
                            }]
                        },
                        dataSource: roProductData.products
                    }).appendTo(container);
              }
          }
      });
  });
</script>
@endsection
