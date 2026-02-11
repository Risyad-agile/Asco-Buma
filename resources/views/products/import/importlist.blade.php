@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form method="POST" action="{{route('product.import.save')}}">
    @csrf 
    <div id="gridContainer"></div> 
    <input id="txtProduct" type="text" name="iproducts"  value="" placeholder="Store ID" hidden>
    <input id="txtCompId" type="text" name="compid"  value="{!!$compid!!}" class="form-control" placeholder="Store ID" hidden>
</form>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  const products={!!$products!!};
  $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Daftar Produk</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
                // useSubmitBehavior: true,
                onClick: function(e) {      
                    window.location = "{{route('home')}}";
                }
            }
        }]
    });
  $("#gridContainer").dxDataGrid({
        dataSource: products,
        // keyExpr: "product_id",
        showBorders: true,
        allowColumnResizing: true,
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        groupPanel: {
            visible: true
        },
        columns: [
            {
                dataField: "product_id",
                caption: "ID Produk",
                value:"[AUTO NUMBER]",
                visible:false,
                // width:150,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "productcat.prodcat_desc",
                caption: "Kategori Produk",
            },{
                dataField: "brand.brand_name",
                caption: "Merek",
                // visible:false,
            },{
                dataField: "product_name",
                caption: "Nama Produk",
                // visible:false,
                // width:125,
            },{
                dataField: "product_stockstate",
                caption: "Status Stok",
                // width:125,
                visible:false,
                lookup: {
                    dataSource: [{"product_stockstate":"1","product_stockstate_desc":"Stock"},
                            {"product_stockstate":"0","product_stockstate_desc":"Non Stock"}],
                    valueExpr: "product_stockstate",
                    displayExpr: "product_stockstate_desc",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            },

        ],
        toolbar: {
        items: [
            'searchPanel','addRowButton',
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'save',
                    hint: 'Simpan',
                    useSubmitBehavior: true,
                    onClick() {
                        $("#txtProduct").val(JSON.stringify(products)); 
                    },
                },
            },
        ],}, 
    });
});
</script>
@endsection
