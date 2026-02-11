@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div id="toolbar"></div>
            <div class="card-body">
                <form method="POST" action="{{route('product.import.type.selected')}}">
                    @csrf 
                    <label>Pilih Jenis</label>
                    <div id="simpleList"></div>
                    <div id="btnLanjut"></div>
                    <input id="txtId" type="text" name="id"
                            class="form-control" placeholder=" ID" hidden >
                </form>
            </div>
        </div>
    </div>
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
            return $("<div class='long-title'><h3>Jenis Import Produk</h3></div>");
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
    var listDataSource = new DevExpress.data.DataSource({
        loadMode: "raw",
        key: "id",
        load: function (key) {
          return $.ajax({
              url: "{{route('offmemtypes.list.state','2')}}"
          })
      },
    });

    var listWidget = $("#simpleList").dxList({
        dataSource: listDataSource, 
        itemTemplate: function(data, index) {
            return data.offmem_type_desc;
        },
        editEnabled: true,
        height: 300,
        allowItemDeleting: false,
        itemDeleteMode: "toggle",
        showSelectionControls: true,
        searchEnabled: true,
        selectionMode: "single",
    }).dxList("instance");

 
    $("#btnLanjut").dxButton({
        type: "success",
        text: "Lanjut",
        useSubmitBehavior: true,
        onClick: function(e) {      
            $("#txtId").val(listWidget.option("selectedItemKeys")); 
            var txtId=document.getElementById("txtId").value;
            console.log(txtId);
            if(txtId==""){
                DevExpress.ui.notify({
                    message: "Silakan pilih Jenis Impor...",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
                e.preventDefault();
                // event.preventDefault();
                return false;
            }
      }
    });
 
});
</script>
@endsection


