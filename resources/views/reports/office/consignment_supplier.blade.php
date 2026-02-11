@extends('layouts.master')
@section('content')
<div class="row justify-content-center" >
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div class="card-header"><div id="toolbar"></div></div>
            <div class="card-body">
                <form method="POST" action="{{route('farma.report.products.consignment.stock.main')}}">
                    @csrf 
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="dx-field-label">Tanggal Awal</div>
                            <div class="dx-field-value">
                                <div id="tglawal"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-10">
                            <div class="dx-field-label">Tanggal Akhir</div>
                            <div class="dx-field-value">
                                <div id="tglakhir"></div>
                            </div>
                        </div>  
                    </div>
                    <label>Pilih Supplier</label>
                    <div id="simpleList"></div>
                    <div id="btnLanjut"></div>
                    <input id="txttglawal" type="hidden" name="tgl_awal">
                    <input id="txttglakhir" type="hidden" name="tgl_akhir">
                    <input id="txtsupplier" type="text" name="supplier_id"
                            class="form-control" placeholder="Supplier ID" hidden>
                    <input id="txtstore" type="text" name="store_id" value='{!!$storeid!!}'
                            class="form-control" placeholder="Store ID" hidden >
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
    $("#tglawal").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        value: new Date(),
        type: 'date',
        onValueChanged(data) { 
            $("#txttglawal").val(getSelectedDate(data.value)); 
        },
    });
    $("#tglakhir").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        value: new Date(),
        onValueChanged(data) {
            $("#txttglakhir").val(getSelectedDate(data.value)); 
        },    
    });
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Supplier Konsinyasi</h3></div>");
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
              url: "{{route('suppliers.consignment')}}"
          })
      },
    });


    var listWidget = $("#simpleList").dxList({
        dataSource: listDataSource, 
        itemTemplate: function(data, index) {
            return data.supplier_name;
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
            $("#txtsupplier").val(listWidget.option("selectedItemKeys")); 
            var txtstore=document.getElementById("txtstore").value;
            var txtsupplier=document.getElementById("txtsupplier").value;
            var txttglawal=document.getElementById("txttglawal").value;
            var txttglakhir=document.getElementById("txttglakhir").value;
            if(txttglawal==""){
                var datebox_awal=$('#tglawal').dxDateBox("instance").option("value");
                $("#txttglawal").val(getSelectedDate(datebox_awal)); 
            }
            if(txttglakhir==""){
                var datebox_akhir=$('#tglakhir').dxDateBox("instance").option("value");
                $("#txttglakhir").val(getSelectedDate(datebox_akhir)); 
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
                // event.preventDefault();
                return false;
            }
      }
    });
});
function getSelectedDate(selectedDate){
    var dtgl=selectedDate.getDate();
    var mtgl=selectedDate.getMonth();
    var ytgl=selectedDate.getFullYear();
    if(dtgl<10){
        dtgl="0"+dtgl;
    }
    mtgl=mtgl+1;
    if(mtgl<10){
        mtgl="0"+mtgl;
    }
    var tgl=ytgl.toString()+"-"+mtgl.toString()+"-"+dtgl.toString();
    return tgl;
}
</script>
@endsection


