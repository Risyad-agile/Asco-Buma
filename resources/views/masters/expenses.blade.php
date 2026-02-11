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
                    return $("<div class='long-title'><h3>Pengaturan Pengeluaran</h3></div>");
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
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('expenses.create')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: "{{route('expenses.store')}}",
              method: "POST",
              data: values
          })
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('zaida/expenses')}}"+"/"+kunci,
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
        keyExpr: "brand_id",
        showBorders: true,
        editing: {
            mode: "popup",
            allowUpdating: true,
            allowAdding:true,
            useIcons:true,
            popup: {
                title: "Update Merek",
                showTitle: true,
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
                dataField: "expense_desc",
                caption: "Pengeluaran",
            },
        ]
    });
});
</script>
@endsection
