@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8" style="margin-top: 25px;">
    <div class="card">
        <div id="toolbar"></div>
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="dx-field-label">Mulai</div>
                    <div class="dx-field-value">
                        <div id="tglawal"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dx-field-label">Akhir</div>
                    <div class="dx-field-value">
                        <div id="tglakhir"></div>
                    </div>
                </div>  
            </div>
            <form method="POST" action="{{route('office.reports.purchase.supplier.product.list')}}">
                @csrf 
                <label>Pilih Toko</label>
                <div id="simpleList"></div>
                <div id="btnLanjut"></div>
                <input id="txttglawal" name="tgl_awal" type="hidden" >
                <input id="txttglakhir" name="tgl_akhir" type="hidden" >
                <input id="txtstoreid" type="text" name="store_id" value="{!!$store->store_id!!}"
                class="form-control" placeholder="Store ID" hidden>
                <input id="txtsupplier" type="text" name="supplier_id"
                        class="form-control" placeholder="Store ID" hidden>
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
            return $("<div class='long-title'><h3>Periode & Supplier</h3></div>");
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

    $("#tglawal").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        value: new Date(),
        onValueChanged(data) {
            var tglawal=getTanggal(data.value);
            $('#txttglawal').val(tglawal);
        },
    });
    $("#tglakhir").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        value: new Date(),
        onValueChanged(data) {
            var tglakhir=getTanggal(data.value);
            $('#txttglakhir').val(tglakhir);
        },
    });

    function getTanggal(tanggal){
        var dtglakhir=tanggal.getDate();
        var mtglakhir=tanggal.getMonth();
        var ytglakhir=tanggal.getFullYear();
        if(dtglakhir<10){
                dtglakhir="0"+dtglakhir;
            }
        mtglakhir=mtglakhir+1;
        if(mtglakhir<10){
            mtglakhir="0"+mtglakhir;
        }
        var  tglakhir=ytglakhir.toString()+"-"+mtglakhir.toString()+"-"+dtglakhir.toString();
        return tglakhir;
    }

    const suppliers={!!$suppliers!!};
    var listDataSource = new DevExpress.data.DataSource({
        loadMode: "raw",
        store: new DevExpress.data.ArrayStore({
        key: 'supplier_id',
        data: suppliers,
      }),
    });

    var listWidget = $("#simpleList").dxList({
        dataSource: listDataSource, 
        displayExpr: 'supplier_name',
        editEnabled: true,
        height: 300,
        itemDeleteMode: "toggle",
        showSelectionControls: true,
        selectionMode: "single",
        onSelectionChanged() {
            $('#txtsupplier').val(listWidget.option('selectedItemKeys'));
        },
    }).dxList("instance");

    $("#btnLanjut").dxButton({
        type: "success",
        text: "Lanjut",
        useSubmitBehavior: true,
        onClick: function(e) {      
            var txtsupplier=document.getElementById("txtsupplier").value;
            var txttglawal=document.getElementById("txttglawal").value;
            var txttglakhir=document.getElementById("txttglakhir").value;
            if(txttglawal==""){
            var tglawal=getTanggal(new Date());
            $('#txttglawal').val(tglawal);
            }
            if(txttglakhir==""){
                var tglakhir=getTanggal(new Date());
                $('#txttglakhir').val(tglakhir);
            }
            if(txtsupplier==""){
                DevExpress.ui.notify({
                    message: "Silakan pilih Toko...",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
                e.preventDefault();
                return false;
            }
      }
    });
 
});
</script>
@endsection


