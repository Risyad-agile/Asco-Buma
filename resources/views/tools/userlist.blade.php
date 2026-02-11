@extends('layouts.master')
@section('content')
<div class="container">
    <div class="long-title"><h3>Daftar Pengguna</h3></div>
    <form id="form-container" class="first-group">
        <div id="form"></div>
        <div class="second-group">
            <div id="btnAdd"></div>
            <div id="gridContainer"></div>
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
  var userDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
            url: "{{route('users.list.load')}}"
          })
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('agile/users/list/update')}}"+"/"+kunci,
              method: "PUT",
              data: values
          })
      }
  });
  
  $("#gridContainer").dxDataGrid({
        dataSource: userDataSource,
        keyExpr: "username",
        selection: {
            mode: "single"
        },
        hoverStateEnabled: true,
        showBorders: true,
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        editing: {
            mode: "popup",
            allowUpdating: true,  
            useIcons: true,
            popup: {
                title: "Update User",
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
        columns: [
            {
                dataField: "username",
                caption: "User Name",
            },
            {
                dataField: "name",
                caption: "Nama",
            },
            {
                dataField: "stores.store_name",
                caption: "Store",
            },
            {
                dataField: "email",
                caption: "Email",
            },
        ],
        onSelectionChanged: function (selectedItems) {
            // var data = selectedItems.selectedRowsData[0];
            // $("#numPay").dxNumberBox('instance').option('value',data.username);
            // $('#mdlUserRight').modal('show');
            
            // if(data) {
            //     console.log(data.name);
            //     // $(".employeeNotes").text(data.Notes);
            //     // $(".employeePhoto").attr("src", data.Picture);
            // }
        }
    });
    $("#numPay").dxNumberBox({
        format: "#,##0.##",
        value: 0,
        height:50,
        readOnly:true,
        rtlEnabled: true,
    });
});

</script>
@endsection
