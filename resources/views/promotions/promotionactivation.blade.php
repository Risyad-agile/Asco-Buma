@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Perubahan Status Aktivasi Promo</h3></div>
      <form id="form-container" class="first-group">
          <div id="form"></div>
          <div class="second-group">
            <div id="toolbar"></div>
              <div id="gridContainer"></div>
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
                        <div class="form-group row">
                            <label for="txtPromoRule" class="col-sm-4 control-label ">Ketentuan</label>
                            <div class="col-sm-8">
                                <input id="txtPromoRule" type="text" class="form-control"  readonly>
                            </div>
                        </div>
                    </div>
                    <div id="simpleList"></div>
                    <input id="txtstores" type="text" name="store_id"
                                class="form-control" placeholder="Store ID" hidden>
                </div>
                <div class="modal-footer">
                <div id="btnActive"></div>
                {{-- <div id="btnDeActive"></div> --}}
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
            </div>
          </div>
        {{-- end of add Product dialog --}}
@endsection


@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#btnActive").dxButton({
        type: "success",
        text: "Update",
        // useSubmitBehavior: true,
        onClick: function(e) {      
            var txtPromoNo=document.getElementById("txtPromoNo").value;
            var txtstores=document.getElementById("txtstores").value;
            var result = DevExpress.ui.dialog.confirm("Perbaharui Aktifasi Promo No "+txtPromoNo+", lanjutkan...??", "Konfirmasi");
            result.done(function (dialogResult) {
                $("#btnActive").dxButton("instance").option("disabled",true);
                // $("#btnDeActive").dxButton("instance").option("disabled",true);
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

    // $("#btnDeActive").dxButton({
    //     type: "success",
    //     text: "Not Aktifkan",
    //     onClick: function(e) {    
    //         var vtransno=$("#txtMutInNo").dxTextBox("instance").option('value');            
    //     }
    // });  

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
                visible:false,
            },{
                dataField: "promo_state",
                caption: "Status Aktif Toko",  
            },
        ],
        onRowClick:function(e){
          data=e.data;          
          $("#btnActive").dxButton("instance").option("disabled",false);
            if(data) {
                $("#txtPromoNo").val(data.promo_no);
                $("#txtPromoName").val(data.promo_desc);
                $("#txtPromoRule").val(data.promo_rule);

                var selectedStores=data.stores;
                //sebagai nilai awal (default) posisi list uncheck
                //jika tidak ada store, artinya promo tersebut belum ada di toko, untuk antisipasi
                //proses sebelumnya, maka list di iterasi untuk di kembalikan ke posisi uncheck
                for (i = 0; i < storecount; i++) {
                    listWidget.selectItem(i);
                    if(listWidget.isItemSelected(i)) {
                        listWidget.unselectItem(i);
                    }
                }

                //jika ada isi store di tabel maka store terpilih tersebut dimasukan kedalam
                //list sebagai default terpilih
                if(Object.keys(selectedStores).length>0){
                    var selectedKeys = listWidget.option("selectedItemKeys");
                    selectedStores.forEach(myFunction);
                    function myFunction(value,index) {
                        selectedKeys.push(value.store_id);
                    }
                    listWidget.option("selectedItemKeys", selectedKeys);
                }
                
                if(data.promo_no!=null){
                    $('#mdlKonfirmasi').modal('show');
                }

            }
        },
    });

    var updateSelectedItems = function(e) {
        var selectedItemKeys = e.component.option("selectedItemKeys");
        var valitem=selectedItemKeys.join(",");
        $("#txtstores").val(valitem);  
    };

    var listDataSource = new DevExpress.data.DataSource({
        loadMode: "raw",
        key: "store_id",
        load: function (key) {
          return $.ajax({
              url: "{{route('store.getnamebycompany')}}"
          })
        },
        requireTotalCount:true
    });

    //hitung jumlah data pada list untuk keperluan reset (uncheck)
    //bisa saja menggundakan metode lain, misalnya mengirimkan jumlah
    //store dari server pada saat load
    var storecount=0;
    var deferred = $.Deferred();
    listDataSource.load().done(function (result, extra) {
        deferred.resolve(extra.totalCount);
    });
    $.when(deferred).done(function(count){
        storecount=count;
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

 $("#toolbar").dxToolbar({
    items: [{
              location: 'center',
              locateInMenu: 'never',
              template: function() {
                  return $("<div class='toolbar-label'><b>Pilih Promo untuk mengaktifkan di Toko</b></div>");
              }
            }]
    });

});
</script>
@endsection
