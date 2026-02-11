@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Pengaturan Jenis Keanggotaan</h3></div>
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
              url: "{{route('membertypes.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('membertypes.store')}}",
              method: "POST",
              data: values
          })
      },
      update: function (key, values) {
          var kunci= key.memtype_id;
          return $.ajax({
              url: "{{URL::to('agile/membertypes')}}"+"/"+kunci,
              method: "PUT",
              data: values
          })
      }
  });
  DevExpress.config({
    floatingActionButtonConfig: {
            icon: "rowfield",
            position: {
                of: "#gridContainer",
                my: "right bottom",
                at: "right bottom",
                offset: "-16 -16"
            }
        }
    });

  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        showBorders: true,
        editing: {
            mode: "popup",
            allowUpdating: true,
            allowAdding:true,
            useIcons:true,
            popup: {
                title: "Update Jenis Member",
                showTitle: true,
                position: {
                    my: "top",
                    at: "top",
                    of: window
                },
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
                caption: "ID",
                dataField: "memtype_id",
                value:"[AUTO NUMBER]",
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "memtype_desc",
                caption: "Deskripsi",
            },{
                dataField: "memtype_min_value",
                caption: "Transaksi Minimal",
                dataType:"number",
                format: "fixedPoint",
                validationRules: [{
                    type: "required",
                    message: "Jumlah harus di isi...",
                },{
                    type: "range",
                    min:0,
                    message: "Jumlah harus positif",
                },],
            },{
                dataField: "memtype_min_periode",
                caption: "Bulan Pencapaian",
                dataType:"number",
                format: "fixedPoint",
                validationRules: [{
                    type: "required",
                    message: "Jumlah harus di isi...",
                },{
                    type: "range",
                    min:0,
                    message: "Jumlah harus positif",
                },],
            },
        ]
    });
});
</script>
@endsection


 