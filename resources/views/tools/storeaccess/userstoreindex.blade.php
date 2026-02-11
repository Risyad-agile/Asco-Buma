@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form method="POST" action="{{route('store.access.main')}}">
    @csrf 
    <div id="gridContainer"></div>
    <input id="txtUserName" type="text" name="username" class="form-control" hidden >
    <input id="txtUserState" type="text" name="userstate" class="form-control" hidden>
</form>
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
            return $("<div class='long-title'><h3>Pengaturan Akses Multi Toko</h3></div>");
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
  var user = {!! $users !!};
  $("#gridContainer").dxDataGrid({
        dataSource: user,
        keyExpr: "username",
        selection: {
            mode: "single"
        },
        hoverStateEnabled: true,
        // columnHidingEnabled: true,
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
                hidingPriority: 0,
            },{
                dataField: "name",
                caption: "Nama",
                hidingPriority: 1,
            },{
                dataField: "stores.store_name",
                caption: "Store",
                hidingPriority: 1,
            },{
                dataField: "email",
                caption: "Email",
                hidingPriority: 2,
            },
        ],
        toolbar: {
        items: [
            'searchPanel','columnChooserButton',
            {
                location: 'center',
                locateInMenu: 'never',
                template: function() {
                    return $("<div>Khusus Pengguna Manager & Store Leader</div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'fas fa-store',
                    hint: 'Pilih Toko',
                    useSubmitBehavior: true,
                    onClick: function(e) {
                        var txtUserName=document.getElementById("txtUserName").value;
                        if(txtUserName==""){
                            DevExpress.ui.notify({
                                message: "Silakan Pilih Pengguna",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                        $("#txtUserState").val("STORES"); //kirim perintah update ke server
                    },},
            // },{
            //     location: 'after',
            //     widget: 'dxButton',
            //     options: {
            //         icon: 'close',
            //         hint: 'Tutup',
            //         onClick() {
            //             window.location = "{{route('home')}}";
            //         },},
            },
        ],},
        onSelectionChanged: function (selectedItems) {
            var data = selectedItems.selectedRowsData[0]; 
            $("#txtUserName").val(data.username);
        }
    });

});

</script>
@endsection
