@extends('layouts.master')
@section('content')
    <div class="loadpanel"></div>
    @csrf 
    <div class="long-title"><h3>User Token Accress Request List</h3></div>
    <div id="gridContainer"></div>
    <input id="txtUserId" type="text" name="userid" class="form-control" hidden >
    <input id="txtUserState" type="text" name="userstate" class="form-control" hidden>


    {{-- add receive desc dialog --}}
    <div class="modal fade" id="mdlChangeCompany" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary">
            <h3 id="mdlChangeCompanyTitle" class="modal-title"><span class="badge badge-primary">Update Company</span></h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="form-container" class="first-group">
              <div id="form"></div>
          </form>
        </div>
        </div>
    </div>
    </div>
  {{-- end receive desc dialog --}}

@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.fn.serializeObject = function(){
      var o = {};
      var a = this.serializeArray();
      $.each(a, function() {
          if (o[this.name] !== undefined) {
              if (!o[this.name].push) {
                  o[this.name] = [o[this.name]];
              }
              o[this.name].push(this.value || '');
          } else {
              o[this.name] = this.value || '';
          }
      });
      return o;
  };
 
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "id",
          load: function() {
              return jsonFile;
          }
      });
    };
  var userDataSource = {!!$users!!}
  
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
        columns: [
            {
                dataField: "name",
                caption: "Name",
            },{
                dataField: "email",
                caption: "Email",
            },{
                dataField: "organization",
                caption: "Organization",
            },{
                dataField: "approved",
                caption: "State",
            },
        ],
        toolbar: {
        items: [
            'searchPanel','columnChooserButton',
            {
                location: 'center',
                locateInMenu: 'never',
                template: function() {
                    return $("<div class='toolbar-label'><b>REQUEST LIST</b></div>");
                }
            },{                
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: "key",
                    hint: 'Generate Token',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        var txtUserId=document.getElementById("txtUserId").value;
                        if(txtUserId==""){
                            swal({
                                title: "Warning",
                                icon: "warning",
                                text: "Please Choose user to create API Token",
                                value: true,
                                visible: true,
                                className: "",
                                closeModal: true,
                            });
                            return false;
                        }
                        $('#mdlChangeCompany').modal('show');
                        // registratiornAccepted(txtUserId);     
                        }
                    },
                },{
                    location: 'after',
                    widget: 'dxButton',
                    locateInMenu: 'auto',
                    options: {
                        icon: "trash",
                        hint: 'Reject Request',
                        // useSubmitBehavior: true,
                        onClick: function(e) {      
                            var txtUserId=document.getElementById("txtUserId").value;
                            var txtUserState=document.getElementById("txtUserState").value;
                            if(txtUserId==""){
                                swal({
                                    title: "Warning",
                                    icon: "warning",
                                    text: "Please Choose user to create API Token",
                                    value: true,
                                    visible: true,
                                    className: "",
                                    closeModal: true,
                                });
                                // e.preventDefault();
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
    
    
    const companies={!!$companies!!}
    const roles = {!! $role !!};
    $("#form").dxForm({
        colCount:1,
        items: [{
            dataField: "comp_id",
            label:{
                text:"Company",
            },
            editorType: 'dxSelectBox',
            editorOptions: {
                items: companies,
                displayExpr: "comp_name",
                valueExpr: "id",
            },
        },{
            dataField: "role_name",
            label:{
                text:"Role",
            },
            editorType: 'dxSelectBox',
            editorOptions: {
                items: roles,
                displayExpr: "name",
                valueExpr: "name",
            }
        },{
            itemType: "button",
            horizontalAlignment: "right",
            buttonOptions: {
                text: "Submit",
                type: "success",
                // useSubmitBehavior: true,
                onClick: function(e) {      
                    $('#mdlChangeCompany').modal('hide');
                    var form =$('#form-container').serializeObject();
                    var txtUserId=document.getElementById("txtUserId").value;
                    var compid=form['comp_id'];
                    var rolename=form['role_name'];
                    if(compid==""){
                        swal({
                            title: "Warning",
                            icon: "warning",
                            text: "Please Choose Company to continue generate API Token",
                            value: true,
                            visible: true,
                            className: "",
                            closeModal: true,
                        });
                        // e.preventDefault();
                        return false;
                    }
                    if(rolename==""){
                        swal({
                            title: "Warning",
                            icon: "warning",
                            text: "Please Choose Role for User",
                            value: true,
                            visible: true,
                            className: "",
                            closeModal: true,
                        });
                        // e.preventDefault();
                        return false;
                    }
                    registratiornAccepted(txtUserId,compid,rolename)
                    
                }
            }
        },]
  });
});
function registratiornAccepted(txtUserId,compid,rolename) {
    Swal.fire({
        title: 'Confirmation',
        text: "Accepted Request and Generate Token",
        icon: 'question',
        showCancelButton: true,
        // confirmButtonColor: '#3085d6',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        // $("#btnPay").dxButton("instance").option("disabled",true);
        if (result.value) {
            loadPanel.show(); 
        }
    }) 
    const loadPanel = $('.loadpanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#employee' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        onShown() {
                $.ajax({
                    url: "{{route('users.registration.accepted')}}",
                    method: "POST",
                    data: JSON.stringify({userid:txtUserId,compid,rolename}),                
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function (data) {
                        if(data.code != 200) {
                            Swal.fire({
                                icon: data.status,
                                title: "Validation Error",
                                text: data.message,
                                footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                            })
                        }else{
                            Swal.fire({
                                icon: data.status,
                                title: "Succesful Send",
                                text: data.message,
                            }).then((result) => {
                                window.location.href = '{{route('users.registration.list')}}';
                            });
                        }
                        loadPanel.hide(); 
                        return false;
                    },    
                    error: function(data) {
                        swal({
                            title: "Validation Error",
                            icon: data.status,
                            text: data.message,
                            value: true,
                            visible: true,
                            className: "",
                            closeModal: true,
                        });
                        loadPanel.hide(); 
                        return false;
                    }            
                });  
            },
            onHidden() {
                // showEmployeeInfo(employee);
            },
    }).dxLoadPanel('instance');
}
</script>
@endsection
