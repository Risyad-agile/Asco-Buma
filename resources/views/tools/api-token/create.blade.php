@extends('layouts.master')
@section('content')
    <div id="toolbar"></div>
    <form id="form-container" class="first-group">
        <div id="form"></div> 
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

    var user={!!$user!!};  
    var formWidget = $("#form").dxForm({
        formData:user,
        readOnly: false,
        showColonAfterLabel: true,
        showValidationSummary: true,
        items: [{ 
                label: {
                    text: "Email",
                },
                dataField: "email", 
                editorOptions:{
                    readOnly:true,
                }
            },{       
                label: {
                    text: "Company ",
                },
                dataField: "company.comp_name", 
                editorOptions:{
                    readOnly:true,
                }
            },{       
                label: {
                    text: "Nama Pengguna",
                },
                dataField: "name", 
                editorOptions:{
                    readOnly:true,
                }
            },{
                label: {
                    text: "API Token",
                },
                dataField:'user_token', 
                editorType: "dxTextArea",
                editorOptions: {
                    // width: "300px",
                    height: 150,
                    placeholder : "Ketentuan Promo"
                }
            // },{
            // itemType: "button",
            // horizontalAlignment: "right",
            // buttonOptions: {
            //     text: "Simpan",
            //     type: "success",
            //     useSubmitBehavior: true
            // }
        }]
    }).dxForm("instance");
  
    $("#toolbar").dxToolbar({
    items: [{
                location: 'center',
                locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Generate User API Token</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: 'message',
                    hint: 'Email Token and Close',
                    onClick() {
                        var form =$('#form-container').serializeObject();
                        $.ajax({
                            url: "{{route('users.api.token.send.email')}}",
                            method: "POST",
                            data: JSON.stringify({user}),           
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
                                        window.location = "{{route('users.api.token.index')}}";
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
                        
                        
                    },
                },
        
        }]
    });
});
</script>
@endsection
