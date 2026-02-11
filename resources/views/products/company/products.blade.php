@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" data-autohide="true" >
        <div class="toast-header">
            <strong class="me-auto">Informasi Produk Baru</strong>
            <small>Kidswa Farma</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Agar tidak terjadi duplikasi data produk, silakan cari produk yang akan anda tambahkan sebelum
            membuat Produk Baru, hal ini untuk menjaga integritas data, terimakasih atas kerjasamanya
        </div>
    </div>
</div>
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
                return $("<div class='long-title'><h3>Pengaturan Produk</h3></div>");
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
  const prodcats= {!! $prodcat !!};
  const brands={!! $brands !!}
  const units={!!$units!!}
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('products.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('products.save.active')}}",
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
              url: "{{URL::to('farma/products')}}"+"/"+kunci,
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
                }
                else {
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
            url: "{{URL::to('farma/products')}}"+"/"+kunci,
            method: "DELETE",
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
        dataSource: gridDataSource,//prods, 
        showBorders: true,
        allowColumnResizing: true,
        editing: {
            mode: "popup",
            useIcons: true,
            allowUpdating: true,
            allowDeleting:true,
            allowAdding:true,
            popup: {
                title: "Product Update",
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
                caption: "ID Produk",
                value:"[AUTO NUMBER]",
                visible:false,
                // width:150,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "prodcat_id",
                caption: "Kategori Produk",
                // width:200,
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: prodcats,
                    valueExpr: "id",
                    displayExpr: "prodcat_desc",
                }
            },{
                dataField: "brand_id",
                caption: "Merek",
                visible:false,
                // width:175,
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                setCellValue: function(rowData, value) {
                    rowData.brand_id = value;
                },
                lookup: {
                    dataSource: brands,
                    valueExpr: "id",
                    displayExpr: "brand_name",
                }
            },{
                dataField: "unit_id",
                caption: "Satuan",  
                visible:false,
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: units,
                    valueExpr: "id",
                    displayExpr: "unit_name",
                }
            },{
                dataField: "product_barcode",
                caption: "Barcode",
                visible:false,
                // width:125,
            },{
                dataField: "product_name",
                caption: "Nama",     
                validationRules: [{
                    type: "required",
                    message: "Silakan di isi deskripsi produk...",
                },], 
            },{
                dataField: "product_dot",
                caption: "DOT",
                // width:125,
                visible:false,
                lookup: {
                    dataSource: [{"product_unit":"HIJAU"},{"product_unit":"BIRU"},
                            {"product_unit":"MERAH"},{"product_unit":"NO DOT"}],
                    valueExpr: "product_unit",
                    displayExpr: "product_unit",
                },
                validationRules:[{
                        type: "required",
                        message: "Pilih dari daftar",}],
            // },{
            //     dataField: "product_stock_state",
            //     caption: "Status Stok",
            //     // width:125,
            //     visible:false,
            //     lookup: {
            //         dataSource: [{"product_stock_state":"1","product_stock_state_desc":"Stock"},
            //                 {"product_stock_state":"0","product_stock_state_desc":"Non Stock"}],
            //         valueExpr: "product_stock_state",
            //         displayExpr: "product_stock_state_desc",
            //     },
            //     validationRules:[{
            //             type: "required",
            //             message: "Pilih dari daftar",}],
            },

        ],
    });
});
</script>
@endsection
