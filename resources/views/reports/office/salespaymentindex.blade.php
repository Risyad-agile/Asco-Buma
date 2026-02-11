@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div id="toolbar"></div> 
            <div class="card-body">
                <form method="POST" action="{{route('office.reports.salespayment.open')}}">
                    @csrf 
                    <div class="row justify-content-center">
                        <div id="calendar"></div>
                    </div>
                    <label>Pilih Toko</label>
                    <div id="simpleList"></div>
                    <div id="btnLanjut"></div>
                    <input id="txtstores" type="text" name="store_id"
                            class="form-control" placeholder="Store ID" hidden>
                    <input id="txtmonthyear" type="text" name="month_year"
                            class="form-control" placeholder="Month Year" hidden>
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
            return $("<div class='long-title'><h3>Pilih Periode dan Lokasi Laporan</h3></div>");
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
        key: "store_id",
        load: function (key) {
          return $.ajax({
                url: "{{route('store.getnamebycompany.exho')}}"
          })
      },
    });

    var calendar = $("#calendar").dxCalendar({
        value: new Date(),
        disabled: false,
        firstDayOfWeek: 0,
        // zoomLevel: 'year',
        maxZoomLevel: 'year', 
        minZoomLevel: 'century',  
        onValueChanged: function(data) {

        },
        onOptionChanged: function(data) {
            var tanggal=new Date(data.value);
            var bulan=String(tanggal.getMonth()+1);
            var tahun=String(tanggal.getFullYear());
            if(bulan.length==1){
                bulan="0".concat(bulan);
            }
            // console.log(bulan.concat(tahun));
            $("#txtmonthyear").val(bulan.concat(tahun)); 
        }
    }).dxCalendar("instance");

    $("#datebox").dxDateBox({
        format: "date",
        width: 200,
        displayFormat: "Myyyy",
        maxZoomLevel: 'year', 
        minZoomLevel: 'century', 
        type: "date",
        value: new Date(),
    });

    var listWidget = $("#simpleList").dxList({
        dataSource: listDataSource, 
        itemTemplate: function(data, index) {
            return data.store_name;
        },
        editEnabled: true,
        height: 200,
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
            var monthyear=document.getElementById("txtmonthyear").value; 
            var txtstores=document.getElementById("txtstores").value;
            if(monthyear==""){
                var tanggal=new Date();
                var bulan=String(tanggal.getMonth()+1);
                var tahun=String(tanggal.getFullYear());
                if(bulan.length==1){
                    bulan="0".concat(bulan);
                }
                $("#txtmonthyear").val(bulan.concat(tahun)); 
                // DevExpress.ui.notify({
                //     message: "Silakan pilih Bulan...",
                //     position: {
                //         my: "center top",
                //         at: "center top"
                //     }
                // }, "warning", 3000);
                // e.preventDefault(); 
                return false;
            }
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


