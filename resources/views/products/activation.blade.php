@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Aktivasi Produk Pada Toko/ Resto</h3></div>
    <div id="tabProd"></div>
    <div class="loadpanel"></div>
    @if($message = Session::get('success'))
        <div class="aler aler-success alert-block">
    <strong>{{$message}}</strong>
</div>
@endif
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  const stores ={!! $stores !!};  
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {  
            var deferred = $.Deferred();
            $.ajax({
                url: "{{URL::to('farma/products/activation/load')}}",
                method: "POST",
                data: {stores},
                dataType: "json",
                success: function (data) {
                    deferred.resolve(data)
                },
            });
            return deferred.promise();
      },
      update: function (key, activestate) {
            var productid= key.id;
            $.ajax({
                url: "{{URL::to('farma/products/activation/update')}}"+"/"+productid,
                method: "PUT",
                data: {activestate,stores,productid},
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
                    DevExpress.ui.notify("Produk Berhasil di Perbaharui"); 
                }
                $("#tabProd").dxDataGrid("instance").refresh();
                return false;
            },
          });
          return false;
      }
    });
    const loadPanel = $('.loadpanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#gridContainer' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        message:"Harap Menunggu, sedang proses...",
        onShown() {
        },
        onHidden() {
        },
    }).dxLoadPanel('instance');

    $("#tabProd").dxDataGrid({
        dataSource: gridDataSource, 
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
                caption: "ID",
                visible:false,
                //   width:150,
            },{
                dataField:"productcategory.prodcat_desc",
                caption:"Kategori",
                //   width:200      
            },{
                dataField:"product_name",
                caption:"Nama Produk",              
            },{
                dataField:"product_state",
                dataType:"boolean",
                caption:"Aktifkan",
                editorType: "dxSwitch", 
                editorOptions: { 
                    switchedOffText:"Tidak",
                    switchedOnText:"Ya",
                    width:80,
                },  
                //   width:100,
            },
        ],
        toolbar: {
        items: [
            'searchPanel','saveButton','revertButton','columnChooserButton',
            {
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "selectall",
                    hint: 'Ubah Masal',
                    onClick: function(e) {      
                        Swal.fire({
                            title: "Proses ini akan mengaktifkan semua produk di Apotek, lanjutkan...??",
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Aktifkan', 
                            cancelButtonText: 'Batal', 
                        }).then((result) => {
                            loadPanel.show();
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "POST",
                                    url: "{{route('products.activation.all')}}",
                                    data: JSON.stringify({stores:stores}),
                                    contentType: "application/json; charset=utf-8",
                                    dataType: "json",
                                    success: function (data)
                                    {
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
                                                title: "OK",
                                                icon: data.status,
                                                text: data.message,
                                                value: true,
                                                visible: true,
                                                className: "",
                                                closeModal: true,
                                            });
                                            // window.location = "{{route('products.activation.index')}}";
                                            loadPanel.hide();
                                            return false;
                                        }                            
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
                            }
                        })
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'close',
                    hint: 'Tutup',
                    onClick() {
                        window.location = "{{route('home')}}";
                    },
                },
            },
        ],},
      onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "product_state")  {
                e.editorName = "dxSwitch"; 
            }
        },
      onEditingStart: function(e){
          if (e.column.dataField != "product_state" ) {
             e.cancel = true;
          }
       },
       onRowUpdated:function(e){
            DevExpress.ui.notify("Produk Berhasil di Aktifasi"); 
       },
  });
  
});
</script>
@endsection


