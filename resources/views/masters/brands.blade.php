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
                return $("<div class='long-title'><h3>Pengaturan Merek Produk</h3></div>");
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
            url: "{{route('brands.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
            url: "{{route('brands.store')}}",
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
              url: "{{URL::to('farma/brands')}}"+"/"+kunci,
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
            url: "{{URL::to('agile/brands')}}"+"/"+kunci,
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
   
  DevExpress.config({
    floatingActionButtonConfig: {
            icon: "rowfield",
            position: {
                of: "#gridContainer",
                my: "right bottom",
                at: "right bottom",
                offset: "-16 -16"
            }
        }
    });

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
                title: "Update Merek",
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
                label: {
                    text: "ID Merek",
                },
                dataField: "id",
                visible:false, 
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "brand_name",
                caption: "Nama Merek",
            },
        ],
    });
});
</script>
@endsection
