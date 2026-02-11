@extends('layouts.master')
@section('content')
  <body class="dx-viewport">
      <div class="long-title"><h3>Perubahan Status Aktifasi Promo</h3></div>
      <form id="form-container" class="first-group">
          <div id="form"></div>
          <div class="second-group">
              <div id="gridContainer"></div>
              <label class="col-sm-4 control-label ">Pilih Promo untuk mengaktifkan di Toko</label>
          </div>
      </form>
      {{-- add products dialog --}}
          <div class="modal fade" id="mdlKonfirmasi" role="dialog">
            <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header bg-primary">
                    <h3 class="modal-title"><span class="badge badge-primary">Aktifkan Promo di Toko</span></h3>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="form-group row">
                            <label for="txtPromoNo" class="col-sm-4 control-label ">ID Promo</label>
                            <div class="col-sm-8">
                                <input id="txtPromoNo" type="text" name="promo_no"
                                    class="form-control"  readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="txtPromoName" class="col-sm-4 control-label ">Nama Promo</label>
                            <div class="col-sm-8">
                                <input id="txtPromoName" type="text" name="promo_desc"
                                    class="form-control"  readonly>
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <label for="txtStatus" class="col-sm-4 control-label ">Status</label>
                            <div class="col-sm-8">
                                <input id="txtStatus" type="text" class="form-control"  readonly>
                            </div>
                        </div> --}}
                        {{-- <div class="form-group row">
                            <label for="tglakhirpromo" class="col-sm-4 control-label ">Akhir Periode</label>
                            <div class="col-sm-8">
                                <div id="tglakhirpromo"></div>
                            </div>
                        </div> --}}
                    </div>
                    <div id="simpleList"></div>
                    <input id="txtstores" type="text" name="store_id"
                                class="form-control" placeholder="Store ID" hidden>
                </div>
                <div class="modal-footer">
                <div id="btnActive"></div>
                <div id="btnDeActive"></div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
            </div>
          </div>
        {{-- end of add Product dialog --}}
  </body>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
//   var dtgl="1";
//   var mtgl="1";
//   var ytgl="2019";
  $("#tglakhirpromo").dxDateBox({
        displayFormat: "dd-MM-yyyy",
        // applyValueMode: "useButtons",
        width:"50%",
        // value: new Date(ytgl,mtgl,dtgl),  //value: new Date(2017, 0, 3), tahun, bulan-1, tanggal
        // label:{
        //   text:"Tanggal Penjualan",
        // },
        // onValueChanged: function(e){
        //   // alert(e.actionValue);

        //   var tanggal=e.value;
        //   var dtgl=tanggal.getDate();
        //   var mtgl=tanggal.getMonth();
        //   var ytgl=tanggal.getFullYear();


        // }
    });

  $("#btnActive").dxButton({
        type: "success",
        text: "Aktifkan",
        // useSubmitBehavior: true,
        onClick: function(e) {      
            var txtPromoNo=document.getElementById("txtPromoNo").value;
            var txtstores=document.getElementById("txtstores").value;
            if(txtstores==""){
                DevExpress.ui.notify({
                    message: "Silakan pilih Toko..",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
                e.preventDefault();
                return false;
            }
            var result = DevExpress.ui.dialog.confirm("Aktifasi Promo No "+txtPromoNo+" dilanjutkan ??", "Konfirmasi");
            result.done(function (dialogResult) {
                $("#btnActive").dxButton("instance").option("disabled",true);
                $("#btnDeActive").dxButton("instance").option("disabled",true);
                if(dialogResult){
                    $.ajax({
                        type: "POST",
                        url: "{{route('promotions.activatepromostores')}}",
                        data: JSON.stringify({promono:txtPromoNo,storeids:txtstores}),
                        contentType: "application/json; charset=utf-8",
                        dataType: "json",
                        beforeSend: function(){},
                        success: function(response){},
                        failure: function(errMsg) {
                            alert(errMsg);
                        },
                        complete: function(jqXHR) {
                            if(jqXHR.readyState === 4) {
                                DevExpress.ui.notify({
                                    message: "Aktifasi Promo di beberapa Toko telah berhasil dilakukan",
                                    position: {
                                        my: "center top",
                                        at: "center top"
                                    }
                                }, "success", 3000);
                                $("#btnActive").dxButton("instance").option("disabled",true);
                            }
                            location.reload();
                        },
                    });
                }
            }); 
      }
    });
    $("#btnDeActive").dxButton({
        type: "success",
        text: "Not Aktifkan",
        // useSubmitBehavior: true,
        onClick: function(e) {    
            var vtransno=$("#txtMutInNo").dxTextBox("instance").option('value');
            
            
        }
    });  

  var promos={!!$promos!!};
  $("#gridContainer").dxDataGrid({
        dataSource: promos,
        keyExpr: "promo_no",
        selection: {
        mode: "single"
        },
        showBorders: true,
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                dataField: "promo_type",
                caption: "Jenis Promo",
                groupIndex: 0,
                calculateCellValue: function(rowData) {
                    var type="Merchant";
                    if(rowData.promo_type=='1'){
                      type="Discount";
                    }
                    return type;
                  }  
            },{
                dataField: "promo_no",
                caption: "Kode Promo",
                visible:false,
            },{
                dataField: "promo_desc",
                caption: "Nama Promo",
            },{
                dataField: "promo_date_start",
                caption: "Tanggal Mulai",
                dataType:"date",
                format:'dd-MM-yyyy',
            },{
                dataField: "promo_date_end",
                caption: "Tanggal Akhir",
                dataType:"date",
                format:'dd-MM-yyyy',

            },{
                dataField: "promo_rule",
                caption: "Ketentuan",
            },{
                dataField: "promo_state",
                caption: "Status Aktif Toko",  
            },
        ],
        onRowClick:function(e){
          data=e.data;
          console.log(e.data);
          
          $("#btnActive").dxButton("instance").option("disabled",false);
            if(data) {
                var status="";
                switch(data.promo_state) {
                case "0":
                    status="Tidak Aktif";
                    break;
                case "1":
                    status="Aktif";
                    break;
                // case "3":
                //     status="Received";
                //     $("#btnProses").dxButton("instance").option("disabled",true);
                //     break;    
                default:
                    status="Canceled";
                }
                $("#txtPromoNo").val(data.promo_no);
                $("#txtPromoName").val(data.promo_desc);
                $("#txtStatus").val(status);
                if(data.promo_no!=null){
                    $('#mdlKonfirmasi').modal('show');
                }
                
            }
        },
        onSelectionChanged: function (selectedItems) {
            // var data = selectedItems.selectedRowsData[0];
            // $("#btnActive").dxButton("instance").option("disabled",false);
            // if(data) {
            //     var status="";
            //     switch(data.promo_state) {
            //     case "0":
            //         status="Tidak Aktif";
            //         break;
            //     case "1":
            //         status="Aktif";
            //         break;  
            //     default:
            //         status="Canceled";
            //     }
            //     $("#txtPromoNo").val(data.promo_no);
            //     $("#txtPromoName").val(data.promo_desc);
            //     $("#txtStatus").val(status);
            //     $('#mdlKonfirmasi').modal('show');
            // }
        },
    });
    var updateSelectedItems = function(e) {
        var selectedItemKeys = e.component.option("selectedItemKeys");
        var valitem=selectedItemKeys.join(",");
        $("#txtstores").val(valitem);  
        // $("#btnProses").dxButton("instance").option("disabled",false);
    };
    var listDataSource = new DevExpress.data.DataSource({
        loadMode: "raw",
        key: "store_id",
        load: function (key) {
          return $.ajax({
              url: "{{route('store.getnamebycompany')}}"
          })
      },
    });
    var listWidget = $("#simpleList").dxList({
        dataSource: listDataSource, 
        itemTemplate: function(data, index) {
            return data.store_name;
        },
        editEnabled: true,
        height: 250,
        allowItemDeleting: false,
        itemDeleteMode: "toggle",
        showSelectionControls: true,
        searchEnabled: true,
        selectionMode: "all",
        onSelectionChanged: updateSelectedItems,
        onItemDeleted: updateSelectedItems
    }).dxList("instance");



});
</script>
@endsection
