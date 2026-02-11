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
  $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Daftar Poin dan Transaksi Anggota</h3></div>");
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
              url: "{{route('members.create')}}"
          })
      },
  });

  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "member_id",
        showBorders: true,
        dateSerializationFormat: "dd-MM-yyyy",
        editing: {
            mode: "form",
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
                // dataField: "member_no",
                // caption: "No Anggota",
                // value:"[AUTO NUMBER]",
                // editorOptions: {
                //     readOnly:true,
                // },
            // },{
                dataField: "membertypes.memtype_desc",
                caption: "Jenis Member", 
            },{
                dataField: "member_id",
                caption: "ID Anggota",
            },{
                dataField: "member_name",
                caption: "Nama",
            },{
                dataField: "member_points",
                caption: "Jumlah Point",
                dataType:"number",
                format: "fixedPoint",
            },{
                dataField: "member_total_trans",
                caption: "Total Belanja",
                dataType:"number",
                format: "fixedPoint",
 
            },
        ],

    });
});
</script>
@endsection
