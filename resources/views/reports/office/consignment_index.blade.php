@extends('layouts.master')
@section('content')
<div class="row justify-content-center" >
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div class="card-header"><div id="toolbar"></div></div>
            <div class="card-body">
                @csrf 
                <label>Pilih Toko</label>
                <div id="simpleList"></div>
                <div id="btnLanjut"></div>
                <input id="txtstores" type="hidden" name="store_id">
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
            return $("<div class='long-title'><h3>Laporan Penjualan Konsinyasi</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
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
              url: "{{route('store.company')}}"
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
        selectionMode: "single",
    }).dxList("instance");

    $("#btnLanjut").dxButton({
        type: "success",
        text: "Lanjut",
        useSubmitBehavior: true,
        onClick: function(e) {      
            $("#txtstores").val(listWidget.option("selectedItemKeys")); 
            var storeid=document.getElementById("txtstores").value;
            if(storeid==""){
                Swal.fire({
                    icon: 'error',
                    title: "Silakan Pilih Toko",
                    showConfirmButton: false,
                    timer: 1000
                }); 
                e.preventDefault(); 
                return false;
            }
            window.location ="{{URL::to('farma/reports/products/consignment/stock/supplier/store')}}"+"/"+storeid;
      }
    });
 
});

</script>
@endsection


