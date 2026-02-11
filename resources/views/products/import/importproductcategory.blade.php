@extends('layouts.master')
@section('content')
    <div class="content"> 
        <div id="toolbar"></div>
        <form method="POST" action="{{route('product.import.prodcat.save')}}">
            @csrf 
        <div id="gridContainer"></div> 
        <input id="txtProdcats" type="text" name="iprodcats"  value="" class="form-control" placeholder="Store ID" hidden>
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
            return $("<div class='long-title'><h3>Pengaturan Kategori Produk</h3></div>");
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
    const prodcats={!!$prodcats!!}; 
    const importprodcats={!!$importprodcats!!};
    $("#gridContainer").dxDataGrid({
        dataSource: importprodcats,
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
                dataField: "prodcat_desc",
                caption: "Kategori",
            },{
                dataField: "prodcat_id",
                caption: "Maping Kategori",
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: prodcats,
                    valueExpr: "prodcat_id",
                    displayExpr: "prodcat_desc",
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
                    return $("<div class='toolbar-label'><b>Kosongkan Maping Untuk Kategori Baru</b></div>");
                } 
            },{         
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'arrowright',
                    hint: 'Lanjut',
                    useSubmitBehavior: true,
                    onClick() {
                        $("#txtProdcats").val(JSON.stringify(importprodcats)); 
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
