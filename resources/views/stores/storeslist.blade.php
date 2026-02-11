@extends('layouts.master')
@section('content')
<div class="content">
      <div class="long-title"><h3>Pengaturan Toko</h3></div>
      <form id="form-container" class="first-group">
          <div class="first-group">
              <div id="gridContainer"></div>
              <div id="btnSave" align="right"></div>
          </div>
      </form>
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
            url: "{{route('stores.list.load')}}"
          })
      },
      update: function (key, values) {
          var kunci= key.store_id;
          return $.ajax({
              url: "{{URL::to('agile/stores')}}"+"/"+kunci,
              method: "PUT",
              data: values
          })
      }
  });
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "store_id",
        showBorders: true,
        editing: {
            mode: "popup",
            allowUpdating: true,
            useIcons: true,
            popup: {
                title: "Update Toko",
                showTitle: true,
                width: 700,
                height: 500,
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
                dataField: "store_id",
                caption: "ID Toko",
                value:"[AUTO NUMBER]",
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "store_name",
                caption: "Nama Toko",
            },{
                dataField: "store_address",
                caption: "Alamat",
            },{
                dataField: "store_province",
                caption: "Propinsi",
                visible:false,
            },{
                dataField: "store_city",
                caption: "Kota",    
            },{
                dataField: "store_distric",
                caption: "Kecamatan",   
                visible:false, 
            },{
                dataField: "store_zip_code",
                caption: "Kode Pos",  
                visible:false,
            },{
                dataField: "store_pin",
                caption: "PIN Toko",    
                // type:"number",
                format: "fixedPoint",
                validationRules: [{
                    type: "stringLength",
                    min:6, 
                    max:6,
                    message: "Panjang PIN 6 Digit..."},{
                    type: "pattern",
                    pattern: /\d{6}$/,
                    message: "6 Digit PIN berupa Angka"
                }]
            },{
                dataField: "store_struck_anote",
                caption: "Struk Note 1",  
                validationRules: [{
                    type: "stringLength",
                    min:0, 
                    max:30,
                    message: "Panjang  Note 1, 36 Digit..."}]
            },{
                dataField: "store_struck_bnote",
                caption: "Struk Note 2",  
                validationRules: [{
                    type: "stringLength",
                    min:0, 
                    max:30,
                    message: "Panjang  Note 2, 36 Digit..."}]
            },{
                dataField: "store_type",
                caption: "Jenis Toko",   
                width: 125,
                lookup: {
                    dataSource: [
                        {'store_type':'0','store_type_desc':'Online Store'}, 
                        {'store_type':'1','store_type_desc':'Retail'},
                        {'store_type':'2','store_type_desc':'Resto'}, 
                        {'store_type':'3','store_type_desc':'Retail Lite'},
                    ],
                    displayExpr: "store_type_desc",
                    valueExpr: "store_type",
                }, 
            },
        ]
    });
});
</script>
@endsection
