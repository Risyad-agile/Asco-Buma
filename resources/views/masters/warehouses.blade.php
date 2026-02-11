@extends('layouts.master')
@section('content')
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <div id="btnSave" align="right"></div>
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
            return $("<div class='long-title'><h3>Pengaturan Gudang/ Dapur</h3></div>");
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
              url: "{{route('warehouses.create')}}"
          })
      },
      insert: function (values) {
        $.ajax({
            type: 'POST',
            url: '{{route('warehouses.store')}}',
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
                }else {
                    swal({
                        title: "Success",
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
        var kunci= key.warehouse_id;
        $.ajax({
            url: "{{URL::to('agile/warehouses')}}"+"/"+kunci,
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
        var kunci= key.warehouse_id;
        $.ajax({
            url: "{{URL::to('agile/warehouses')}}"+"/"+kunci,
            method: "DELETE",
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
        // keyExpr: "warehouse_id",
        showBorders: true,
        editing: {
            mode: "popup",
            allowUpdating: true, 
            allowAdding:true,
            allowDeleting:true,
            useIcons:true,
            popup: {
                title: "Update Toko",
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
                dataField: "warehouse_id",
                caption: "ID",
                value:"[AUTO NUMBER]",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "warehouse_name",
                caption: "Nama Gudang/ Toko",
                validationRules: [{
                    type: "required",
                    message: "Masukan Nama Gudang/ Dapur..."
                }]
            },{
                dataField: "warehouse_desc",
                caption: "Deskripsi",
            },{
                dataField: "warehouse_index",
                caption: "Urutan",
                dataType:"number",
                format: "fixedPoint",
                editorType: "dxNumberBox",
                editorOptions: { 
                    dataType:"number",
                    format: "##",
                },
                validationRules:[{
                    min:0,
                    max:99,
                    type: "range",
                    message: "Masukan Nomor Urut Wajar..."
                },{
                    type: "stringLength",
                    max: 2,
                    message: "Maksimum Dua Karakter"}], 
            },{
                dataField: "warehouse_ip",
                caption: "Alamat IP",
                // dataType:"number",
                // format: "fixedPoint",
                // editorType: "dxNumberBox",
                editorOptions: { 
                    // dataType:"number",
                    // format: "###.###.###.###",
                },
                validationRules:[{
                //     min:0,
                //     max:99,
                //     type: "range",
                //     message: "Masukan Nomor Urut Wajar..."
                // },{
                    type: "stringLength",
                    max: 15,
                    message: "Maksimum Dua Karakter"}], 
            },
        ],
        // toolbar: {
        // items: [
        //     'searchPanel','addRowButton',
        //     {
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
    });
});
</script>
@endsection
