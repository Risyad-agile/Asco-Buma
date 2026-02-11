@extends('layouts.master')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Aktivasi Produk Close Loop Emoney</div>
                <div class="card-body">
                    {!! Form::open(['id' => 'frm','route' => 'active.product.pay.main','class' => 'form-horizontal']) !!}
                        <label>Pilih Toko</label>
                        <div id="simpleList"></div>
                        <div id="btnLanjut"></div>
                        <!-- <div id="selectedStores"></div> -->
                        <input id="txtstores" type="text" name="store_id"
                                class="form-control" placeholder="Store ID" hidden>
                    {!! Form::close()!!}
                </div>
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

    var listDataSource = new DevExpress.data.DataSource({
        loadMode: "raw",
        key: "store_id",
        load: function (key) {
          return $.ajax({
              url: "{{route('store.getnamebycompany.exho')}}"
          })
      },
    });


    var listWidget = $("#simpleList").dxList({
        dataSource: listDataSource, 
        itemTemplate: function(data, index) {
            return data.store_name;
        },
        editEnabled: true,
        height: 300,
        allowItemDeleting: false,
        itemDeleteMode: "toggle",
        showSelectionControls: true,
        searchEnabled: true,
        selectionMode: "all",
    }).dxList("instance");

 
    $("#btnLanjut").dxButton({
        type: "success",
        text: "Lanjut",
        useSubmitBehavior: true,
        onClick: function(e) {      
            $("#txtstores").val(listWidget.option("selectedItemKeys")); 
            var txtstores=document.getElementById("txtstores").value;
            if(txtstores==""){
                DevExpress.ui.notify({
                    message: "Silakan pilih Toko...",
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


