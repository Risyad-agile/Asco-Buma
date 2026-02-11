@extends('layouts.master')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Pengguna Baru</div>
                <div class="card-body">
                <form id="form-container"  class="first-group">
                    <div id="form"></div>
                </form>
                </div>
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
  var roles = {!! $role !!};
  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "id",
          load: function() {
              return jsonFile;
          }
      });
    };

  var formUser= {!! $user!!};
  var formWidget = $("#form").dxForm({
    dataSource:formUser,
    readOnly: false,
    showColonAfterLabel: true,
    showValidationSummary: true,
    validationGroup: "userData",
        items: [{ 
                label: {
                    text: "User Name",
                },
                dataField: "username", 
                validationRules: [{
                    type: "required",
                    message: "User Name harus di isi..."
                },{
                    type: "pattern",
                    pattern: "[0-9]+$",
                    message: "User Name di isi dengan nomor handphone"
                }]
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
                    text: "Alamat email",
                },
                dataField: "email",
                validationRules: [{
                    type: "required",
                    message: "Email harus di isi"
                }, {
                    type: "email",
                    message: "Email tidak valid"
                }]

            },{
                dataField: "password",
                editorOptions: {
                    mode: "password"
                },
                validationRules: [{
                    type: "required",
                    message: "Password harus di isi"
                }]
            },{
                label: {
                    text: "Konfirmasi Password"
                },
                editorType: "dxTextBox",
                editorOptions: {
                    mode: "password"
                },
                validationRules: [{
                    type: "required",
                    message: "Konfirmasi Password harus di isi"
                }, {
                    type: "compare",
                    message: "'Password' dan 'Konfirmasi Password' tidak sesuai",
                    comparisonTarget: function() {
                        return formWidget.option("formData").password;
                    }
                }]
            },{
                dataField: "role",
                editorType: "dxDropDownBox",
                editorOptions: {
                    dataSource: makeAsyncDataSource(roles),
                    displayExpr: "name",
                    valueExpr: "id",
                    placeholder: "Select a value...",
                    contentTemplate: function(e){
                    var value = e.component.option("value"),
                        $dataGrid = $("<div>").dxDataGrid({
                            dataSource: e.component.option("dataSource"),
                            columns: ["name","display_name"],
                            hoverStateEnabled: true,
                            paging: { enabled: true, pageSize: 10 },
                            filterRow: { visible: true },
                            scrolling: { mode: "infinite" },
                            height: 345,
                            selection: { mode: "multiple" },
                            selectedRowKeys: value,
                            onSelectionChanged: function(selectedItems){
                                var keys = selectedItems.selectedRowKeys;
                                e.component.option("value", keys);
                            }
                        });
                    
                    dataGrid = $dataGrid.dxDataGrid("instance");
                    
                    e.component.on("valueChanged", function(args){
                        var value = args.value;
                        dataGrid.selectRows(value, false);
                    });
                    
                    return $dataGrid;
                }
                },
                validationRules: [{
                    type: "required",
                    message: "Pilih Akses Level"
                }]
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
        $.ajax({
          type: "POST",
          url: "{{route('users.store')}}",
          data: JSON.stringify({ form: form }),
          contentType: "application/json; charset=utf-8",
          dataType: "json",
          success: function(response){
          },
          failure: function(errMsg) {
            alert(errMsg);
          },
          complete: function(jqXHR) {
            if(jqXHR.readyState === 4) {
                DevExpress.ui.notify({
                    message: "Data Pengguna Berhasil di Simpan",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "success", 3000);
                location.reload();
            }
          }
        });
    });
});
</script>
@endsection


