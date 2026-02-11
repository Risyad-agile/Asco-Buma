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
                    return $("<div class='long-title'><h3>Pengaturan Apotik</h3></div>");
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
    var companies={!!$companies!!};

    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('stores.create')}}"
          })
      },
      insert: function (values) {
        $.ajax({
            type: 'POST',
            url: '{{route('stores.store')}}',
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
        var kunci= key.id;
        $.ajax({
            url: "{{URL::to('zaida/stores')}}"+"/"+kunci,
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
            url: "{{URL::to('zaida/stores')}}"+"/"+kunci,
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
        showBorders: true,
        columnHidingEnabled: true,
        editing: {
            mode: "popup",
            allowAdding: true, 
            allowUpdating: true, 
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
            pageSize: 15
        },
        columns: [
            {
                dataField: "id",
                caption: "ID Apotik", 
                visible:false, 
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "comp_id",
                caption: "Perusahaan",
                validationRules:[{
                    type: "required",
                    message: "Pilih dari daftar",}],
                lookup: {
                    dataSource: companies,
                    valueExpr: "id",
                    displayExpr: "comp_brand",
                }
            },{
                dataField: "store_name",
                caption: "Nama Apotik",
                validationRules: [{
                    type: "required",
                    message: "Masukan Nama Apotik..."
                }]
            },{
                dataField: "store_type",
                caption: "Jenis Apotik",   
                visible:false, 
                // width: 125,
                lookup: {
                    dataSource: [
                        // {'store_type':'0','store_type_desc':'Online Store'}, 
                        {'store_type':'1','store_type_desc':'Apotik Tanpa Shift'},
                        {'store_type':'2','store_type_desc':'Apotik Dengan Shift'}, 
                        {'store_type':'3','store_type_desc':'Apotik Rumah Sakit'},
                    ],
                    displayExpr: "store_type_desc",
                    valueExpr: "store_type",
                }, 
                validationRules: [{
                    type: "required",
                    message: "Pilih Jenis Toko dari Daftar..."
                }],                
            },{
                dataField: "store_address",
                caption: "Alamat",
                visible:false, 
                validationRules: [{
                    type: "required",
                    message: "Masukan Alamat..."
                }]
            },{
                dataField: "store_phone",
                caption: "Telepon",   
                visible:false, 
            },{
                dataField: "store_province",
                caption: "Propinsi",
                visible:false, 
            },{
                dataField: "store_city",
                caption: "Kota",   
                visible:false, 
            },{
                dataField: "store_distric",
                caption: "Kecamatan",   
                visible:false, 
            },{
                dataField: "store_zip_code",
                caption: "Kode Pos",  
                visible:false,
            },
        ]
    });
});
</script>
@endsection
