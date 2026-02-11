@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card" style="margin-top: 20px;">
            <div class="card-header"><div class="long-title"><h3>Pilih Lokasi Produk</h3></div></div>
            <div class="card-body">
                {!! Form::open(['id' => 'frm','route' => 'product.import.main','class' => 'form-horizontal']) !!}
                    <label>Pilih Lokasi</label>
                    <div id="store-origin" style="margin-top: 10px;"></div>
                    <div id="store-destination" style="margin-top: 10px;"></div>
                    {{-- <div id="simpleList"></div> --}}
                    <div id="btnLanjut" style="margin-top: 10px;"></div>
                    <!-- <div id="selectedStores"></div> -->
                    <input id="txtstoresorigin" type="text" name="storeorigin" class="form-control" placeholder="Store ID" hidden>
                    <input id="txtstoresdestination" type="text" name="storedestination" class="form-control" placeholder="Store ID" hidden>
                    <input id="txttype" type="text" name="type" class="form-control" value="4" placeholder="OFF MEMO" hidden>
                {!! Form::close()!!}
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
    const stores={!!$stores!!}
    $('#store-origin').dxSelectBox({
        items: stores,
        placeholder: 'Pilih Lokasi Asal',
        showClearButton: true,
        displayExpr: 'store_name',
        valueExpr: 'store_id',
        onValueChanged(data) {
            // DevExpress.ui.notify(`The value is changed to: "${data.value}"`);
            $("#txtstoresorigin").val(data.value); 
            
        },
    });
    $('#store-destination').dxSelectBox({
        items: stores,
        placeholder: 'Pilih Lokasi Tujuan',
        showClearButton: true,
        displayExpr: 'store_name',
        valueExpr: 'store_id',
        onValueChanged(data) {
            // DevExpress.ui.notify(`The value is changed to: "${data.value}"`);
            $("#txtstoresdestination").val(data.value); 
        },
    });

    $("#btnLanjut").dxButton({
        type: "success",
        text: "Lanjut",
        useSubmitBehavior: true,
        onClick: function(e) {      
            var txtstoresorigin=document.getElementById("txtstoresorigin").value;
            var txtstoresdestination=document.getElementById("txtstoresdestination").value;
            if(txtstoresorigin==txtstoresdestination){
                DevExpress.ui.notify({
                    message: "Toko Asal dan Tujuan harus berbeda...",
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


