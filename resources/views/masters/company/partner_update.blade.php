@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
  <div class="card" style="margin-top: 20px;">
    <div id="toolbar"></div>
    <div class="card-body">
      <form id="form-container" class="first-group">
        <div id="form"></div>
      </form>
    </div>
  </div>
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
    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Update Company Credential</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Keluar Tanpa Simpan', 
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            }]
    });
    const company={!!$company!!};
    const types = ['ENVIZI','CLIENT-ACCSETUP','CLIENT-ACCSTYLE','CLIENT-CSRDATA'];
    $("#form").dxForm({
      formData:company,
      colCount: 1,
      items:[{
          dataField: "comp_name",
          label:{
            text:"Company Name",
          },
          editorOptions: { 
              placeholder : "Name of Company",
          }
        },{
          dataField: "comp_email",
          label:{
            text:"Email ",
          },
          editorOptions: {
          }      
        },{
          dataField: "organization.org_name",
          label:{
            text:"Envizi Organization",
          },
          editorOptions: {
            readOnly:true,
          }      
        },{
          dataField: "comp_address",
          label:{
            text:"Company Address",
          },
          editorType: "dxTextArea",
            editorOptions: {
                height: 100,
                placeholder : "Company Address"
              }  
        },{
          itemType: "button",
          horizontalAlignment: "right",
          buttonOptions: {
              text: "Update",
              type: "success", 
              onClick: function(e) {      
                  var form =$('#form-container').serializeObject();
                  if(form['comp_name']==""){
                      swal({
                          title: "Warning",
                          icon: "warning",
                          text: "Please Fill Company Name",
                          value: true,
                          visible: true,
                          className: "",
                          closeModal: true,
                      });
                      return false;
                  }
                  if(form['comp_email']==""){
                      swal({
                          title: "Warning",
                          icon: "warning",
                          text: "Please Fill Email Address",
                          value: true,
                          visible: true,
                          className: "",
                          closeModal: true,
                      });
                      return false;
                  }
                  updateCompany(form)
              }
          }
      },]
  }); 
});
function updateCompany(form) {  
    const compid="{!!$company->id!!}";
    Swal.fire({
        title: 'Confirmation',
        text: "Are you sure want to Update these Data...?",
        icon: 'question',
        showCancelButton: true, 
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.value) {
            $.ajax({
                type: "PUT",
                url: "{{URL::to('partner/company')}}"+"/"+compid,
                data: JSON.stringify({form:form}),           
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
                            title: "Succesful Update",
                            text: data.message,
                        }).then((result) => {
                            window.location = "{{route('home')}}";
                        });
                    }
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
                    return false;
                }            
            });
        }
    }) 
}
</script>
@endsection
