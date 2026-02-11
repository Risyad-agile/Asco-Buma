@extends('layouts.master')
@section('content')
  <body class="dx-viewport">
    <div class="long-title"><h3>Aktivasi Toko Online</h3></div>
    <div class="container">
        <form id="form-container" class="first-group">
            <div id="gridContainer"></div>
            <div id="btnActivateAll"></div>
        </form>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



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
              url: "{{route('members.onlinestore.load')}}"
          })
      },

      update: function (key, values) {
          var kunci= key.member_no;
          return $.ajax({
                url: "{{URL::to('agile/members/store/online/update')}}"+"/"+kunci,
                method: "PUT",
                data: values,
                complete:function(jqXHR) {              
                    if(jqXHR.statusText == "OK") {
                        DevExpress.ui.notify({
                            message: "Aktifasi Online Store Berhasil",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "success", 3000);
                    }
                }
            });
      },
  });


   $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        // keyExpr: "member_id",
        showBorders: true,
        dateSerializationFormat: "dd-MM-yyyy",
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
                dataField: "member_no",
                caption: "No Anggota",
                value:"[AUTO NUMBER]",
                // visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "member_id",
                caption: "ID (Mobile No)",
                validationRules: [{
                  type: "required",
                  message: "Harus di isi",
                },{
                  type: "stringLength",
                  max:20,
                  message: "Maksimum 20 Karakter",
                }]
            },{
                dataField: "member_card_no",
                caption: "Nomor Kartu",
                visible:false,  
            },{
                dataField: "member_name",
                caption: "Nama",
            },{
                dataField: "member_birth_date",
                caption: "Tanggal Lahir",
                visible:false,
                dataType: "date",
                format: "dd-MM-yyyy",
            },{
                width:150,
                dataField: "member_store_activation",
                dataType:"boolean",
                caption: "Aktifkan",
            },
        ],
        onEditorPreparing: function (e) {  
            if (e.parentType == "dataRow" && e.dataField == "member_store_activation")  
                e.editorName = "dxSwitch";  
            },
        onEditingStart: function(e){
            if (e.column.dataField != "member_store_activation" ) {
                e.cancel = true;
            }
        },
        onRowUpdated:function(e){
            DevExpress.ui.notify("Aktivasi Pembayaran di Toko Berhasil di Perbaharui"); 
            // var btnUpdate=$("#btnUpdate").dxButton("instance").option("disabled",false);
        },
    });
});
 // save penerimaan
//  $("#btnActivateAll").dxButton({
//       text: "Aktifkan Semua",
//       type: "success",
//       width: 125,
//       onClick: function(e) {
           
//       }
//   });

 
</script>
@endsection
