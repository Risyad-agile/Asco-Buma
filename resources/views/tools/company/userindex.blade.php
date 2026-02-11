@extends('layouts.master')
@section('content')
<div id="toolbar"></div> 
<form method="POST" action="{{route('users.company.main')}}">
    @csrf 
    <div class="second-group">
        <div id="gridContainer"></div>
    </div>
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
            return $("<div class='long-title'><h3>Pengaturan Pengguna</h3></div>");
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
        toolbar: {
        items: [
            'searchPanel','columnChooserButton',
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'plus',
                    hint: 'Tambah Pengguna Baru',
                    useSubmitBehavior: true,
                    onClick:function(e) {
                        var txtUserName=document.getElementById("txtUserName").value;
                        $("#txtUserState").val("NEW"); //kirim perintah buat baru ke server
                        if(txtUserName!=""){
                            $("#txtUserName").val(""); //supaya ke server jadi null
                        }
                    },},
            },{                
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'edit',
                    hint: 'Perbaharui Data Pengguna',
                    useSubmitBehavior: true,
                    onClick: function(e) {      
                        var txtUserName=document.getElementById("txtUserName").value;
                        if(txtUserName==""){
                            DevExpress.ui.notify({
                                message: "Silakan User Name (Nomor Ponsel User)",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                        $("#txtUserState").val("UPDATES"); //kirim perintah update ke server
                    }
                },
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'key',
                    useSubmitBehavior: true,
                    hint: 'Reset Password',
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
                    $("#txtUserState").val("RESET"); //kirim perintah update ke server
                  }
                },
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
