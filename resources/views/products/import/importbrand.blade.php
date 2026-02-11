@extends('layouts.master')
@section('content')
    <div class="content">
        <div id="toolbar"></div>
        <form method="POST" action="{{route('product.import.brand.save')}}">
            @csrf 
        <div id="gridContainer"></div> 
        <input id="txtBrands" type="text" name="ibrands"  value="" class="form-control" placeholder="Store ID" hidden>
        <input id="txtCompId" type="text" name="compid"  value="{!!$compid!!}" class="form-control" placeholder="Store ID" hidden>
        </form>
    </div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        } 
    });
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Pengaturan Merek Produk</h3></div>");
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
    const brands={!!$brands!!}; 
    var importbrands={!!$importbrands!!};
    $("#gridContainer").dxDataGrid({
        dataSource: importbrands,
        showBorders: true,
        keyExpr: 'id',
        editing: {
            mode: "batch",
            allowUpdating: true,
            useIcons:true, 
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10 
        },
        columns: [
            {
            //     caption: "Nama Merek",
            //     dataField: "brand_id",
            //     validationRules:[{
            //         type: "required",
            //         message: "Pilih dari daftar",}],
            //     lookup: {
            //         dataSource: brands,
            //         valueExpr: "brand_id",
            //         displayExpr: "brand_name",
            //     } 
            // },{
                dataField: "brand_name",
                caption: "Nama Merek",
            },{
                dataField: "brand_id",
                caption: "Maping Merek",
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: brands,
                    valueExpr: "brand_id",
                    displayExpr: "brand_name",
                }                
            },
        ],
        toolbar: {
        items: [
            'searchPanel','saveButton','revertButton',
            {
                location: 'before',
                locateInMenu: 'never',
                template: function() {
                    return $("<div class='toolbar-label'><b>Kosongkan Maping Untuk Merek Baru</b></div>");
                } 
            },{         
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'arrowright',
                    hint: 'Lanjut',
                    useSubmitBehavior: true,
                    onClick() {
                        // window.location = "{{route('home')}}";
                        $("#txtBrands").val(JSON.stringify(importbrands)); 
                    },
                },
            },
        ],},
    }).dxDataGrid('instance');

    $('#startEditAction').dxSelectBox({
    value: 'click',
    items: ['click', 'dblClick'],
    onValueChanged(data) {
      dataGrid.option('editing.startEditAction', data.value);
    },
  });
});
</script>
@endsection
