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

  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('stores.salestax.load')}}"
          })
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('farma/stores/salestax/update')}}"+"/"+kunci,
              method: "PUT",
              data: values
          })
      }
  });
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        // keyExpr: "store_id",
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
                dataField: "id",
                caption: "ID Toko", 
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "store_name",
                caption: "Nama Toko",
            },{
                dataField: "store_address",
                caption: "Alamat",
                visible:false,
            },{
                dataField: "store_province",
                caption: "Propinsi",
                visible:false,
            },{
                dataField: "store_sales_tax",
                caption: "Pajak Penjualan",   
                dataType:"number",
                format: "percent",
                validationRules: [{
                    min:0,
                    max:1, 
                    type: "range",
                    message: "Nilai antara 0 dan 1, 1=100%"
                }] 
            },{
                dataField: "store_sales_tax_state",
                caption: "Pajak Penjualan", 
                dataType:"boolean",
            },{
                dataField: "store_service_charge",
                caption: "Service Charge",   
                dataType:"number",
                format: "percent",
                validationRules: [{
                    min:0,
                    max:1, 
                    type: "range",
                    message: "Nilai antara 0 dan 1, 1=100%"
                }]         
            },{
                dataField: "service_charge_state",
                caption: "Service Charge", 
                dataType:"boolean",    
            },
        ],
        onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "store_sales_tax_state")  
                {e.editorName = "dxSwitch"; } 
            if (e.parentType == "dataRow" && e.dataField == "service_charge_state")  
                {e.editorName = "dxSwitch"; } 

            },
        onEditingStart: function(e){
          if (e.column.dataField != "store_sales_tax" && e.column.dataField != "store_sales_tax_state"
                && e.column.dataField != "store_service_charge" && e.column.dataField != "service_charge_state"){
             e.cancel = true;
          }
        },
    });
    $("#toolbar").dxToolbar({
    items: [{
                location: 'center',
                locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Pengaturan Pajak Penjualan & Service Charge</h3></div>");
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
});
</script>
@endsection
