@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('users.api.token.main')}}">
    @csrf 
    <div class="long-title"><h3>User API Token List</h3></div>
    <div id="gridContainer"></div>
    <input id="txtUserId" type="text" name="userid" class="form-control" hidden >
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
                dataField: "company.comp_name",
                caption: "Company",
                hidingPriority: 0,
            },{
                dataField: "name",
                caption: "Nama",
                hidingPriority: 1,
            },{
                dataField: "email",
                caption: "Email",
                hidingPriority: 2,
            },{
                dataField: "user_state",
                caption: "Token State",
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
                    return $("<div class='toolbar-label'><b>TOKEN LIST</b></div>");
                }
            },{                
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "key",
                    hint: 'Create API Token ',
                    useSubmitBehavior: true,
                    onClick: function(e) {      
                        var txtUserId=document.getElementById("txtUserId").value;
                        if(txtUserId==""){
                            DevExpress.ui.notify({
                                message: "Please Choose user to create API Token",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "warning", 3000);
                            e.preventDefault();
                            return false;
                        }
                        $("#txtUserState").val("CREATE"); //kirim perintah update ke server
                    }},
                },{
                    location: 'after',
                    widget: 'dxButton',
                    locateInMenu: 'auto',
                    options: {
                        icon: "trash",
                        hint: 'Revoke API Token',
                        useSubmitBehavior: true,
                        onClick: function(e) {      
                            var txtUserId=document.getElementById("txtUserId").value;
                            var txtUserState=document.getElementById("txtUserState").value;
                            if(txtUserId==""){
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
                            $("#txtUserState").val("DELETE"); 
                            // $("#gridContainer").dxDataGrid("instance").refresh();
                            // location.reload();
                        }
                    }
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
            $("#txtUserId").val(data.id);
        }
    });
   
});

</script>
@endsection
