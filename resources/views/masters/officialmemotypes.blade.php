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
                    return $("<div class='long-title'><h3>Pengaturan Jenis Berita Acara</h3></div>");
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
              url: "{{route('offmemtypes.load')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('offmemtypes.save')}}",
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
              url: "{{URL::to('farma/offmemtypes/update')}}"+"/"+kunci,
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
        // keyExpr: "id",
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
                dataField: "id",
                caption: "ID",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "offmem_type_state",
                caption: "Kategori",
                lookup: {
                    dataSource: [{"offmem_type_state":"1","offmem_type_state_desc":"Penyesuaian"},
                            {"offmem_type_state":"2","offmem_type_state_desc":"Import"},
                            {"offmem_type_state":"3","offmem_type_state_desc":"Export"}],
                    valueExpr: "offmem_type_state",
                    displayExpr: "offmem_type_state_desc",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            },{
                dataField: "offmem_type_desc",
                caption: "Deskripsi",
            },
        ]
    });
});
</script>
@endsection
