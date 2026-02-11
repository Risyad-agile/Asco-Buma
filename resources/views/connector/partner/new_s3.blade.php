@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
  <div class="card">
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
    const compid ="{!!$company->id!!}";
    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Create New AWS S3 Connection</h3></div>");
                }

            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Keluar Tanpa Simpan', 
                    onClick: function(e) {      
                      window.location.href = "{{URL::to('asri-core/connector/list/open')}}"+"/"+compid;
                    }
                }
            }]
    });
    
    const types = ['ENVIZI','CLIENT'];
    $("#form").dxForm({
      colCount: 1,
      items:[{
          dataField: "connect_name",
          label:{
            text:"Connection Name",
          },
          editorOptions: { 
              placeholder : "Name of API Connection",
          }
      },{
          dataField: "connect_type",
          label:{
            text:"Type",
          },
          editorType: "dxRadioGroup",
          editorOptions: { 
            items: types, 
            value: types[0],
            layout: 'horizontal',
          }
        },{
          dataField: "connect_storage_code",
          label:{
            text:"Storage Code ",
          },
          editorOptions: {
            placeholder : "write public for not sending to s3",
          }
        },{
          dataField: "connect_url",
          label:{
            text:"URL ",
          },
          editorOptions: {
          }
        },{
          dataField: "connect_email",
          label:{
            text:"Email ",
          },
          editorOptions: {
          }   
        },{
          dataField: "connect_access_key_id",
          label:{
            text:"AWS Access Key ID ",
          },
          editorOptions: {
          }  
        },{
          dataField: "connect_access_key_secret",
          label:{
            text:"AWS Access Key Secret ",
          },
          editorOptions: {
          }   
        },{
          dataField: "connect_remote_folder",
          label:{
            text:"AWS Bucket ",
          },
          editorOptions: {
          }         
        },{
            itemType: "button",
            horizontalAlignment: "right",
            buttonOptions: {
                text: "Save",
                type: "success", 
                onClick: function(e) {      
                    var form =$('#form-container').serializeObject();
                    if(form['connect_url']==""){
                        swal({
                            title: "Warning",
                            icon: "warning",
                            text: "Please Fill URL Address",
                            value: true,
                            visible: true,
                            className: "",
                            closeModal: true,
                        });
                        return false;
                    }
                    if(form['connect_email']==""){
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
                    if(form['connect_token_value']==""){
                        swal({
                            title: "Warning",
                            icon: "warning",
                            text: "Please Fill Bearer Token for Authentication",
                            value: true,
                            visible: true,
                            className: "",
                            closeModal: true,
                        });
                        return false;
                    }
                    saveConnection(form)
                }
            }
      },]
  }); 
});
function saveConnection(form) {
  const compid ="{!!$company->id!!}";
  const protocol="{!!$protocol!!}";
    Swal.fire({
        title: 'Confirmation',
        text: "Are you sure want to Save these Data...?",
        icon: 'question',
        showCancelButton: true, 
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: "{{route('partner.connector.store')}}",
                method: "POST",
                data: JSON.stringify({compid,connect_protocol:protocol,form}),                
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
                            title: "Succesful Save",
                            text: data.message,
                        }).then((result) => {
                          window.location.href = "{{URL::to('asri-core/connector/list/open')}}"+"/"+compid;
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
