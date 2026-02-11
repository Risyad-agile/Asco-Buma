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
                    return $("<div class='long-title'><h3>Pengaturan Jenis Lokasi Meja</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: 'close',
                    hint: 'Tutup',
                    onClick() {
                        window.location = "{{route('home')}}";
                    },
                },
        
        }]
    });
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('spottypes.load')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('spottypes.save')}}",
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
          var kunci= key.spot_type_id;
          return $.ajax({
              url: "{{URL::to('agile/spottypes/update')}}"+"/"+kunci,
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
      }
  });
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "spot_type_id",
        showBorders: true,
        editing: {
            mode: "popup",
            useIcons:true,
            allowUpdating: true,
            allowAdding:true,
            popup: {
                title: "Update Jenis Berita Acara",
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
                dataField: "spot_type_id",
                caption: "ID",
                value:"[AUTO NUMBER]",
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "spot_type_desc",
                caption: "Deskripsi Lokasi",
            },
        ]
    });
});
</script>
@endsection
