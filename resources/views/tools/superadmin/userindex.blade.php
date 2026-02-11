@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('users.main')}}">
    @csrf 
    <div class="long-title"><h3>Pengaturan Pengguna</h3></div>
    <div id="toolbar"></div>
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
            },{
                dataField: "name",
                caption: "Nama",
            },{
                dataField: "email",
                caption: "Email",
            },{
                dataField: "company.comp_name",
                caption: "Perusahaan",
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
                                message: "Silakan Pilih Pengguna",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                        $("#txtUserState").val("UPDATES"); //kirim perintah update ke server
                    },},
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'key',
                    useSubmitBehavior: true,
                    hint: 'Reset Password',
                    onClick() {
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
                        $("#txtUserState").val("CHANGE"); //kirim perintah update ke server
                    },},
                },{
                    location: 'after',
                    widget: 'dxButton',
                    locateInMenu: 'auto',
                    options: {
                        icon: "trash",
                        hint: 'Non Aktifkan Pengguna',
                        useSubmitBehavior: true,
                        onClick: function(e) {      
                            var txtUserName=document.getElementById("txtUserName").value;
                            var txtUserState=document.getElementById("txtUserState").value;
                            swal({
                                title: "Non Aktifkan Pengguna",
                                icon: "error",
                                text: "Pilihan ini Belum Tersedia",
                                value: true,
                                visible: true,
                            });
                            e.preventDefault();
                            return false;
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

                            $("#txtUserState").val("DELETE"); //kirim perintah hapus ke server
                    }}
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'close',
                    hint: 'Tutup',
                    onClick() {
                        window.location = "{{route('home')}}";
                    },},
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
