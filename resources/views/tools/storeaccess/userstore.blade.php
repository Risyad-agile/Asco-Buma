@extends('layouts.master')
@section('content')
    <div class="content">
        <div id="toolbar"></div>
        <form id="form-container" class="first-group">
            <div id="form"></div>
            <div id="gridContainer"></div>
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
            return $("<div class='long-title'><h3>Pilih Toko</h3></div>");
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
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
        var deferred = $.Deferred();
        $.ajax({
            url: "{{URL::to('security/stores/access/load')}}",
            method: "POST",
            data: {user},
            dataType: "json",
            success: function (data) {
                deferred.resolve(data)
            },
            
        });
        return deferred.promise();
      },
      update: function (key, status) {
          var storeid= key.store_id; 
          $.ajax({
              url: "{{URL::to('security/stores/access/update')}}"+"/"+storeid,
              method: "PUT",
              data: {status,user,storeid},
              dataType: "json",
              success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    DevExpress.ui.notify("Produk Berhasil di Perbaharui"); 
                }
                $("#tabProd").dxDataGrid("instance").refresh();
                return false;
            },
          });
          return false;
      }
  });
  var user={!!$user!!}; 
  var formWidget = $("#form").dxForm({
    formData:user,
    readOnly: false,
    showColonAfterLabel: true,
    showValidationSummary: true,
        items: [{ 
                label: {
                    text: "User Name [Ponsel]",
                },
                dataField: "username", 
                editorOptions:{
                    readOnly:true,
                }
            },{       
                label: {
                    text: "Toko/ Resto ",
                },
                dataField: "stores.store_name", 
                editorOptions:{
                    readOnly:true,
                }
            },{       
                label: {
                    text: "Nama Pengguna",
                },
                dataField: "name", 
                validationRules: [{
                    type: "required",
                    message: "Nama Pengguna harus di isi"
                },]
            // },{                
            //     label: {
            //         text: "Store",
            //     },
            //     dataField: "store",
            //     editorType: "dxLookup",
            //     editorOptions: {
            //         dataSource: new DevExpress.data.DataSource({ 
            //             store: stores, 
            //             key: "store_id", 
            //         }),
            //         valueExpr: "store_id",
            //         displayExpr: "store_name"
            //     }
            // },{
            // itemType: "button",
            // horizontalAlignment: "right",
            // buttonOptions: {
            //     text: "Simpan",
            //     type: "success",
            //     useSubmitBehavior: true
            // }
        }]
    }).dxForm("instance");
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "store_id",
        showBorders: true,
        editing: {
            mode: "batch",
            allowUpdating: true,
            useIcons: true,
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                dataField: "store_id",
                caption: "ID Toko",
                value:"[AUTO NUMBER]",
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "store_name",
                caption: "Nama Toko",
            },{
                dataField: "store_address",
                caption: "Alamat",

            },{
              dataField:"store_state",
              dataType:"boolean",
              caption:"Aktifkan",
              editorType: "dxSwitch", 
              editorOptions: { 
                switchedOffText:"Tidak",
                switchedOnText:"Ya",
                width:80,
              },  
            //   width:100,
            },
        ],
        onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "store_state")  {
                e.editorName = "dxSwitch"; 
            }
        },
      onEditingStart: function(e){
          if (e.column.dataField != "store_state" ) {
             e.cancel = true;
          }
       },
       onRowUpdated:function(e){
            DevExpress.ui.notify("Produk Berhasil di Aktifasi"); 
       },
    });
});
</script>
@endsection
