@extends('layouts.master')
@section('content')
  <body class="dx-viewport">
      <div class="long-title"><h3>Fasilitas Anggota/ Mitra</h3></div>
      <form id="form-container" class="first-group">
          <div id="form"></div>
          <div class="second-group">
              <div id="gridContainer"></div>
              <div id="btnSave" align="right"></div>
          </div>
      </form>
  </body>
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
              url: "{{route('members.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('members.store')}}",
              method: "POST",
              data: values
          })
      },
      update: function (key, values) {
          var kunci= key.member_no;
          return $.ajax({
              url: "{{URL::to('store/members')}}"+"/"+kunci,
              method: "PUT",
              data: values
          })
      }
  });
  function moveEditColumnToLeft(dataGrid) {
      dataGrid.columnOption("command:edit", {
          visibleIndex: -1
      });
  }
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "member_id",
        showBorders: true,
        dateSerializationFormat: "dd-MM-yyyy",
        editing: {
            mode: "form",
            allowUpdating: true,
            useIcons: true,
            popup: {
                title: "Update Keanggotaan",
                showTitle: true,
                width: 700,
                height: 345,
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
                dataField: "member_no",
                caption: "No Anggota",
                value:"[AUTO NUMBER]",
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "member_type",
                caption: "Jenis Member",
                width: 125,
                lookup: {
                    dataSource: [
                        {'ID':'1','Name':'Reguler'},
                        {'ID':'2','Name':'Silver'},
                        {'ID':'3','Name':'Gold'},
                    ],
                    displayExpr: "Name",
                    valueExpr: "ID",
                },
                validationRules: [{
                        type: "required",
                        message: "Harus di isi",
                }],
            },{
                dataField: "member_id",
                caption: "No Anggota",
                validationRules: [{
                  type: "required",
                  message: "Harus di isi",
                },{
                  type: "stringLength",
                  max:20,
                  message: "Maksimum 20 Karakter",
                }]
            },{
                dataField: "member_name",
                caption: "Nama",
            },{
                dataField: "member_discount",
                caption: "Margin Discount",
                dataType:"number",
                format: "percent",
                validationRules: [{
                    min:0,
                    max:1, 
                    type: "range",
                    message: "Nilai antara 0 dan 1, 1=100%"
                }]
            },{
                dataField: "member_min_purchase",
                caption: "Min Belanja",
                dataType:"number",
                format: "fixedPoint",
                width:125,
                validationRules: [{
                    min:0,
                    type: "range",
                    message: "Minimal 0..."
                }]
            },
        ],
        onContentReady: function (e) {
            moveEditColumnToLeft(e.component);
        },
    });
});
</script>
@endsection
