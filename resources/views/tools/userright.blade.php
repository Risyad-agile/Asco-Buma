@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div class="card-header"><div id="toolbar"></div></div>
            <div class="card-body">
            <form id="form-container"  class="first-group">
                <div id="form"></div>
            </form>
            <label>Daftar Hak Akses</label>
            <div id="simpleList"></div>
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
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Hak Akses Pengguna</h3></div>");
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

    var user= {!! $user!!};
    var formWidget = $("#form").dxForm({
        readOnly: false,
        showColonAfterLabel: true,
        showValidationSummary: true,
        validationGroup: "userData",
        items: [{ 
                dataField: "userid",
                editorType: "dxDropDownBox",
                editorOptions: {
                    dataSource: makeAsyncDataSource(user),
                    displayExpr: "username",
                    valueExpr: "id",
                    placeholder: "Select a value...",
                    displayExpr: function(item){
                        return item && item.username + " <" + item.name + ">";
                    },
                    showClearButton: true,
                    contentTemplate: function(e){
                    var value = e.component.option("value"),
                        $dataGrid = $("<div>").dxDataGrid({
                            dataSource: e.component.option("dataSource"),
                            columns: ["username","name"],
                            hoverStateEnabled: true,
                            paging: { enabled: true, pageSize: 10 },
                            filterRow: { visible: true },
                            scrolling: { mode: "infinite" },
                            height: 345,
                            selection: { mode: "single" },
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
                }},
                validationRules: [{
                    type: "required",
                    message: "Pilih User",
                }],
            },{
                label: {
                    text: "User Right",
                },
                dataField: "userright",
                editorOptions: {
                    value:'',
                    readOnly:true,
                },
                validationRules: [{
                    type: "required",
                    message: "Pilih Hak Akses",
                }]
            },{
            itemType: "button",
            horizontalAlignment: "right",
            buttonOptions: {
                text: "Simpan",
                type: "success",
                // useSubmitBehavior: true
                onClick: function(e) {    
                    var form =$('#form-container').serializeObject();
                    if (form['userid']=="")
                    {
                        DevExpress.ui.notify({
                            message: "Silakan Pilih User...",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        return false;
                    }
                    $.ajax({
                    type: "POST",
                    url: "{{route('userright.store')}}",
                    data: JSON.stringify({ form: form }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function(data){
                        if(data.code != 200) {
                            swal({
                                title: "Validation Error",
                                icon: data.status,
                                text: data.message,
                                value: true,
                                visible: true,
                                className: "",
                                closeModal: true,
                            });
                            }
                        else {
                            swal({
                                title: "OK",
                                icon: data.status,
                                text: data.message,
                                value: true,
                                visible: true,
                                className: "",
                                closeModal: true,
                            });
                        }
                        window.location = "{{route('home')}}";
                        return false;
                    },
                    });
                },
            }
        }]
    }).dxForm("instance");

    var updateSelectedItems = function(e) {
        var selectedItems = e.component.option("selectedItems");
        var valitem=selectedItems.join(",");
        formWidget.itemOption("userright", "editorOptions", {readOnly:true, value: valitem});     
    };

    var gridDataSource = new DevExpress.data.DataSource({
        loadMode: "raw",
        load: function (key) {
          return $.ajax({
              url: "{{route('userright.role.load')}}"
          })
      },
    });
    var listWidget = $("#simpleList").dxList({
        // dataSource: roles,
        dataSource: gridDataSource, 
        editEnabled: true,
        height: 200,
        allowItemDeleting: false,
        itemDeleteMode: "toggle",
        showSelectionControls: true,
        selectionMode: "multiple",
        onSelectionChanged: updateSelectedItems,
        onItemDeleted: updateSelectedItems
    }).dxList("instance");

});
</script>
@endsection


