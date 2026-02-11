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
            return $("<div class='long-title'><h3>Penentuan Gudang/ Dapur Untuk Kategori Produk</h3></div>");
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
  var warehouse={!!$warehouses!!};
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('productcategorys.create')}}"
          })
      },
      update: function (key, values) {
          var kunci= key.prodcat_id;
          return $.ajax({
              url: "{{URL::to('office/productcategorys')}}"+"/"+kunci,
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
        // keyExpr: "prodcat_id",
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
                dataField: "prodcat_id",
                caption: "ID Kategori",
                value:"[AUTO NUMBER]",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "prodcat_desc",
                caption: "Kategori Produk",
            },{
                dataField: "warehouse_id",
                caption: "Gudang/Dapur",
                // width:200,
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: warehouse,
                    valueExpr: "warehouse_id",
                    displayExpr: "warehouse_name",
                }    
            },
        ],
        // toolbar: {
        // items: [
        //     'searchPanel','saveButton','revertButton',            {
        //         location: 'after',
        //         widget: 'dxButton',
        //         options: {
        //             icon: 'close',
        //             hint: 'Tutup',
        //             onClick() {
        //                 window.location = "{{route('home')}}";
        //             },
        //         },
        //     },
        // ],},
        onEditingStart: function(e){
          if (e.column.dataField != "warehouse_id") {
             e.cancel = true;
          }
      },
    });
});
</script>
@endsection
