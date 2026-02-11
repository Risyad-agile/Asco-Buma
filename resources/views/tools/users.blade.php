@extends('layouts.master')
@section('content')
<div class="container">
    <div class="long-title"><h3>Daftar Penggguna</h3></div>
    <form id="form-container" class="first-group">
        <div id="form"></div>
        <div class="second-group">
            <div id="btnAdd"></div>
            <div id="gridContainer"></div>
        </div>
    </form>
</div>
{{-- add products dialog --}}
<div class="modal fade" id="mdlUserRight" role="dialog">
        <div class="modal-dialog">
          <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Product to the List</h5>
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-horizontal">
                <div class="col-sm-8">
                    <div class="dx-texteditor-input" style='font-size:30px' id="numPay"></div>
                </div>
                </div>
                <div class="form-group">
                    <p id="wrmsg" style="color:red; font-size:12px;"></p>
                </div>
            </div>


            <div class="modal-footer">
              <button id="btnAdd" type="button" class="btn btn-info" data-dismiss="modal">Add to List</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

          </div>
        </div>
      </div>
      {{-- end of add Product dialog --}}
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  var user = {!! $user !!};
   
  $("#gridContainer").dxDataGrid({
        dataSource: user,
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
            var data = selectedItems.selectedRowsData[0];
            $("#numPay").dxNumberBox('instance').option('value',data.username);
            $('#mdlUserRight').modal('show');
            
            if(data) {
                console.log(data.name);
                // $(".employeeNotes").text(data.Notes);
                // $(".employeePhoto").attr("src", data.Picture);
            }
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
