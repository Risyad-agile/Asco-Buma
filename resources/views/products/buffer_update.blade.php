@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="tapProdPrice"></div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var storeid ={!! $store->id !!}
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Penentuan Kuantitas Minimum (Bufffer Stok)</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "selectall",
                hint: 'Ubah Masal',
                // useSubmitBehavior: true,
                onClick: function(e) {      
                    window.location ="{{URL::to('farma/products/buffer/mass/index/')}}"+"/"+storeid;
                }
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
    var store ={!! $store !!}
    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
        var deferred = $.Deferred();
        $.ajax({
            url: "{{route('products.buffer.load')}}",
            method: "POST",
            data: {store},
            dataType: "json",
            success: function (data) {
                deferred.resolve(data)
            },
            
        });
        return deferred.promise();
      },
      update: function (key, buffer) {
        var productid= key.id;
        return $.ajax({
            url: "{{URL::to('farma/products/buffer/update')}}"+"/"+productid,
            method: "PUT",
            data: {buffer,store,productid},
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
                    text: data.message,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
            } 
            return false;
        },              
        });
        return false;
      }
    });

    $("#tapProdPrice").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "id",
        showBorders: true,
        allowColumnResizing: true,
        paging: {
            enabled: false
        },
        editing: {
            mode: "batch",
            allowUpdating: true,
            useIcons: true,
        },
        paging: {
            pageSize: 10
        },
        searchPanel: {
            visible: true,
            highlightCaseSensitive: true,
        },
        columns: [
        {
            dataField: "id",
            caption: "PLU",
            visible:false, 
        },{
            dataField:"productcategory.prodcat_desc",
            caption: "Kategori", 
        },{
            dataField:"product_name",
            caption:"Produk",
        },{
            dataField:"product_buffer_stock",
            width:80,
            caption:"PKM",
            dataType:"number",
            format: "fixedPoint", 
            validationRules: [{
                  min:0,
                  type: "required",
                  message: "Masukan Angka..."
            }]
        },],
        onEditingStart: function(e){
            if (e.column.dataField != "product_buffer_stock") {
                e.cancel = true;
            }
        },
        onRowUpdated:function(e){
            DevExpress.ui.notify("Penentuan Kuantitas Minimum Berhasil di Perbaharui"); 
            // var btnUpdate=$("#btnUpdate").dxButton("instance").option("disabled",false);
        },
    });
});
</script>
@endsection


