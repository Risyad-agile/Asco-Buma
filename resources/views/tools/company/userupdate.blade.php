@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div id="toolbar"></div>
            <div class="card-body">
            <form id="form-container"  class="first-group">
                <div id="form"></div>
            </form>
            </div>
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
  $.fn.serializeObject = function()
  {
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

  var user= {!! $user!!};
  var stores={!!$stores!!};

  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "id",
          load: function() {
              return jsonFile;
          }
      });
    };

    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Perbaharui Pengguna</h3></div>");
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
                    window.location = "{{route('users.company.index')}}";
                }
            }
        }]
    });
  var formWidget = $("#form").dxForm({
    formData:user,
    readOnly: false,
    showColonAfterLabel: true,
    showValidationSummary: true,
        items: [{ 
                label: {
                    text: "User Name [Ponsel]",
                },
                dataField: "username", 
                editorOptions:{
                    readOnly:true,
                }
            },{       
                label: {
                    text: "Toko/ Store ",
                },
                dataField: "stores.store_name", 
                editorOptions:{
                    readOnly:true,
                }
            },{       
                label: {
                    text: "Nama Pengguna",
                },
                dataField: "name", 
                validationRules: [{
                    type: "required",
                    message: "Nama Pengguna harus di isi"
                },]
            },{            
                label: {
                    text: "Default Store",
                },
                dataField: "storeid",
                editorType: "dxLookup",
                editorOptions: {
                    dataSource: new DevExpress.data.DataSource({ 
                        store: stores, 
                        key: "store_id", 
                    }),
                    valueExpr: "store_id",
                    displayExpr: "store_name"
                }
            },{
            itemType: "button",
            horizontalAlignment: "right",
            buttonOptions: {
                text: "Simpan",
                type: "success",
                useSubmitBehavior: true
            }
        }]
    }).dxForm("instance");


    $("#form-container").on("submit", function(e) {
        e.preventDefault();
        var form =$('#form-container').serializeObject();
        var userid='{!! $user->id!!}';
        $.ajax({
          type: "PUT",
          url: "{{URL::to('agile/users/company/update')}}"+"/"+userid,
          data: JSON.stringify({ form: form }),
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          success: function(response){
            if(response.code != 200) {
                swal({
                    title: response.status,
                    icon: response.status,
                    text: response.message,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                }
                else {
                    swal({
                        title: response.status,
                        icon: response.status,
                        text: "Data berhasil diperbaharui",
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                    window.location = '{{route('users.company.index')}}';
                }
                
            },
            error: function(jqXHR, textStatus, errorThrown) {
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
            },
        });
    });
});
</script>
@endsection


