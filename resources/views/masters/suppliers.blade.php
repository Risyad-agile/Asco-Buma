@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="gridContainer"></div> 
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
            return $("<div class='long-title'><h3>Pengaturan Supplier</h3></div>");
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
          return $.ajax({
            url: "{{route('suppliers.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
            url: "{{route('suppliers.store')}}",
              method: "POST",
              data: values,
              dataType: "json",
              success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: "Validation Error",
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                    }
                else {
                    swal({
                        title: "OK",
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                swal({
                    title: "Validation Error",
                    icon: data.status,
                    text: data.message,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                return false;
            }
          });
          return false;
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('farma/suppliers')}}"+"/"+kunci,
              method: "PUT",
              data: values,
              dataType: "json",
              success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: "Ada Kesalahan pada saat Update",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: "Data berhasil diperbaharui",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                swal({
                    title: "Error",
                    icon: "error",
                    text: qXHR.responseText,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                return false;
                }
            });
          return false;
      },
      remove: function (key) {
        var kunci= key.id;
        $.ajax({
            url: "{{URL::to('farma/suppliers')}}"+"/"+kunci,
            method: "DELETE",
            dataType: "json",
            success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: "Ada Kesalahan pada saat Penghapusan",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: "Data berhasil di Hapus",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                swal({
                    title: "Error",
                    icon: "error",
                    text: qXHR.responseText,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                return false;
            }
        });
        return false;
      }  
  });

  const cities={!!$cities!!};
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource, 
        showBorders: true,
        editing: {
            mode: "popup",
            allowUpdating: true,
            allowAdding:true,
            allowDeleting:true,
            useIcons:true,
            popup: {
                title: "Update Supplier",
                showTitle: true,
                position: {
                    my: "top",
                    at: "top",
                    of: window
                }
            }
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                dataField: "id",
                caption: "ID", 
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "supplier_name",
                caption: "Nama Supplier",
                validationRules: [{
                    type: "required",
                    message: "Silakan di isi Nama Supplier...",
                }], 
            },{
                dataField: "supplier_address",
                caption: "Alamat",
            },{
                dataField: "city_id",
                caption: "Kota",
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: cities,
                    valueExpr: "id",
                    displayExpr: "city_name",
                }
            },{
                dataField: "supplier_phone",
                caption: "Telepon",
                visible:false,
            },{
                dataField: "supplier_contact_name",
                caption: "Nama Kontak",    
                visible:false,
            },{
                dataField: "supplier_contact_phone",
                caption: "Telepon Kontak",      
                visible:false,
            },{
                dataField: "supplier_consignment_state",
                caption: "Supplier Konsinyasi",
                // width:125,
                // value:'Supplier',
                // visible:false,
                lookup: {
                    dataSource: [{"supplier_consignment_state":"0","supplier_consignment_state_desc":"Supplier"},
                                {"supplier_consignment_state":"1","supplier_consignment_state_desc":"Konsinyasi"}],
                    valueExpr: "supplier_consignment_state",
                    displayExpr: "supplier_consignment_state_desc",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            },
        ],
    });
});
</script>
@endsection

