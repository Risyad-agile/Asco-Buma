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
                return $("<div class='long-title'><h3>Pengaturan Kategori Produk</h3></div>");
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
              url: "{{route('productcategories.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('productcategories.store')}}",
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
              url: "{{URL::to('farma/productcategories')}}"+"/"+kunci,
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
            url: "{{URL::to('farma/productcategories')}}"+"/"+kunci,
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
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        // keyExpr: "prodcat_id",
        showBorders: true,
        editing: {
            mode: "popup",
            allowUpdating: true,
            allowAdding:true,
            allowDeleting:true,
            useIcons: true,
            popup: {
                title: "Update Produk Kategori",
                showTitle: true,
                width: 700,
                height: 345,
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
                dataField: "prodcat_id",
                caption: "ID Kategori",
                visible:false, 
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "prodcat_desc",
                caption: "Kategori Produk",
                validationRules: [{
                    type: "required",
                    message: "Masukan Deskripsi..."
                }]
            },{
                dataField: "prodcat_division",
                caption: "Divisi",
                editorType: "dxSelectBox",
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: [{"prodcat_division":"MEDIC"},
                                 {"prodcat_division":"NON MEDIC"},],
                        value:"MEDIC",
                        displayExpr: "prodcat_division",
                        valueExpr: "prodcat_division",
                        layout: "horizontal",
                },
            },
        ],
    });
});
</script>
@endsection
