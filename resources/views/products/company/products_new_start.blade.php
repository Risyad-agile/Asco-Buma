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
            Agar tidak terjadi duplikasi data produk, Produk Baru di buat melalui mekanisme pengajuan
            produk akan langsung aktif setelah di setujui oleh admin Kidswa Farma
        </div>
    </div>
</div>
<form method="POST" action="{{route('products.company.update.main')}}">
    @csrf 
    <div id="toolbar"></div>
    <input id="txtProductId" type="text" name="product_id" class="form-control" hidden>
    <input id="txtStatus" type="text" name="state" class="form-control" hidden>
    <div id="toolbar"></div>
    <div id="gridContainer" style="margin-top: 10px;"></div> 
</form>



@endsection

@section('script')
<script type="text/javascript">

$(function(){
    $.fn.serializeObject = function(){
      var o = {};
      var a = this.serializeArray();
      $.each(a, function() {
          if (o[this.name] !== undefined) {
              if (!o[this.name].push) {
                  o[this.name] = [o[this.name]];
              }
              o[this.name].push(this.value || '');
          } else {
              o[this.name] = this.value || '';
          }
      });
      return o;
  };
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
                return $("<div class='long-title'><h3>Daftar Pengajuan Produk Baru</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "rename",
                    hint: 'Pengajuan Produk',
                    useSubmitBehavior: true,
                    onClick: function(e) {      
                        var txtProductId=document.getElementById("txtProductId").value;
                        if(txtProductId==""){
                            DevExpress.ui.notify({
                                message: "Silakan Pilih Produk..",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
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

    var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('products.company.load')}}"
          })
      },
  });


  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,//prods, 
        showBorders: true,
        allowColumnResizing: true,
        // keyExpr: "id",
        selection: {
            mode: "single"
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
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "productcategory.prodcat_desc",
                caption: "Kategori Produk",
            },{
                dataField: "product_name",
                caption: "Nama",     
                validationRules: [{
                    type: "required",
                    message: "Silakan di isi deskripsi produk...",
                },], 
            },{
                dataField: "brand.brand_name",
                caption: "Status",
            },

        ],
        onSelectionChanged: function (selectedItems) {
            var data = selectedItems.selectedRowsData[0];
            $("#txtProductId").val(data.id);
            $("#txtStatus").val("UPDATE");
        },        
    });
});
</script>
@endsection
