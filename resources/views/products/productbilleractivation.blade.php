@extends('layouts.master')
@section('content')
    <div class="content">
        <div class="long-title"><h3>Aktivasi Produk Biller</h3></div>
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
            url: "{{route('products.biller.load')}}"
          })
      },
      update: function (key, values) {
          var prodcat_group= key.prodcat_group;
          return $.ajax({
              url: "{{URL::to('agile/products/biller/update')}}"+"/"+prodcat_group,
              method: "PUT",
              data: {values,prodcat_group}
          })
      }
  });
  $("#tabProd").dxDataGrid({
      dataSource: gridDataSource,
      keyExpr: "prodcat_group",
      showBorders: true,
      paging: {
          enabled: false
      },
      editing: {
          mode: "batch",
          allowUpdating: true,
          useIcons: true, 
      },
      paging: {
          pageSize: 8
      },
      searchPanel: {
          visible: true,
          highlightCaseSensitive: true,
      },

      columns: [
          {
              dataField: "prodcat_group",
              caption: "Produk Biller",
              disabled:true,
          },{
              dataField:"prodcat_group_state",
              dataType:"boolean",
              caption:"Status",
          },
      ],
      onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "prodcat_group_state")  
                e.editorName = "dxSwitch";  
            },
      onEditingStart: function(e){
          if (e.column.dataField != "prodcat_group_state" ) {
             e.cancel = true;
          }
      },

  });
  
});
</script>
@endsection

