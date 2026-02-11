@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Perubahan Status Produk Konsinyasi</h3></div>
    <div id="tabProd"></div>
</div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  var gridDataSource = new DevExpress.data.DataSource({
    load: function (key) {
        return $.ajax({
            url: "{{route('products.company.state.consignment.load')}}"
        })
    },
    update: function (key, activestate) {
          var productid= key.id;
          $.ajax({
              url: "{{URL::to('farma/products/company/state/consignment/update')}}"+"/"+productid,
              method: "PUT",
              data: {activestate,productid},
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

  $("#tabProd").dxDataGrid({
      dataSource: gridDataSource, 
      showBorders: true,
      allowColumnResizing: true,
      paging: {
          enabled: false
      },
      columnChooser: {
            enabled: true
        },
      editing: {
          mode: "batch",
          allowUpdating: true,
          useIcons: true,
        //   selectTextOnEditStart: true,
            // startEditAction: "click"
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
              dataField:"product_qty",
              caption:"Stok",
              dataType:"number",
              format: "fixedPoint",
              visible:false,
            //   width:100,
          },{
              dataField:"product_state",
              dataType:"boolean",
              caption:"Produk Konsinyasi",
              editorType: "dxSwitch", 
              editorOptions: { 
                switchedOffText:"Ya",
                switchedOnText:"Bukan",
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


