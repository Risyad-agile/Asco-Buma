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
  $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Pengaturan Akses Toko<</h3></div>");
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
  var companys = {!!$comp!!}
  var stores={!!$stores!!}

  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "id",
          load: function() {
              return jsonFile;
          }
      });
    };
    var makeAsyncDataSourceComp = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "comp_id",
          load: function() {
              return jsonFile;
          }
      });
    };

  var formWidget = $("#form").dxForm({
    // dataSource:formUser,
    readOnly: false,
    showColonAfterLabel: true,
    showValidationSummary: true,
    validationGroup: "userData",
        items: [{ 
                dataField: "User",
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
                }
                },
                validationRules: [{
                    type: "required",
                    message: "Pilih User",
                }]
            // },{
            //     dataField: "Company",
            //     editorType: "dxDropDownBox",
            //     editorOptions: {
            //         dataSource: makeAsyncDataSourceComp(companys),
            //         displayExpr: "comp_brand",
            //         valueExpr: "comp_id",
            //         placeholder: "Select a value...",
            //         displayExpr: function(item){
            //             return item && item.comp_id + " <" + item.comp_brand + ">";
            //         },
            //         showClearButton: true,
            //         contentTemplate: function(e){
            //         var value = e.component.option("value"),
            //             $dataGrid = $("<div>").dxDataGrid({
            //                 dataSource: e.component.option("dataSource"),
            //                 columns: ["comp_brand","comp_legal_name"],
            //                 hoverStateEnabled: true,
            //                 paging: { enabled: true, pageSize: 10 },
            //                 filterRow: { visible: true },
            //                 scrolling: { mode: "infinite" },
            //                 height: 345,
            //                 selection: { mode: "single" },
            //                 selectedRowKeys: value,
            //                 onSelectionChanged: function(selectedItems){
            //                     var keys = selectedItems.selectedRowKeys;
            //                     e.component.option("value", keys);
            //                 }
            //             });
                    
            //         dataGrid = $dataGrid.dxDataGrid("instance");
                    
            //         e.component.on("valueChanged", function(args){
            //             var value = args.value;
            //             dataGrid.selectRows(value, false);
            //         });
                    
            //         return $dataGrid;
            //     }
            //     },
            //     validationRules: [{
            //         type: "required",
            //         message: "Pilih User",
            //     }]
            },{                
                label: {
                    text: "Store",
                },
                dataField: "store",
                editorType: "dxLookup",
                editorOptions: {
                    dataSource: new DevExpress.data.DataSource({ 
                        store: stores, 
                        key: "store_id", 
                    }),
                    // dataSource: function(options) {
                    //     return {
                    //         store: stores,
                    //         // filter: options.data ? ["StateID", "=", options.data.StateID] : null
                    //     };
                    // },
                    valueExpr: "store_id",
                    displayExpr: "store_name"
                }
                // editorOptions: {
                //     value:'',
                //     readOnly:true,
                // },
                // validationRules: [{
                //     type: "required",
                //     message: "Pilih Hak Akses",
                // }]
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


    // $("#lookup-grouped").dxLookup({
    //     dataSource: new DevExpress.data.DataSource({ 
    //         store: employeesTasks, 
    //         key: "ID", 
    //         group: "Assigned"
    //     }),
    //     grouped: true,
    //     closeOnOutsideClick: true,
    //     showPopupTitle: false,
    //     displayExpr: "Subject"
    // });

    $("#form-container").on("submit", function(e) {
        e.preventDefault();
        var form =$('#form-container').serializeObject();
        $.ajax({
          type: "POST",
          url: "{{route('storeaccess.store')}}",
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
                    message: "Hak Akses Pengguna Berhasil di Perbaharui",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "success", 3000);
                window.location = '{{route('home')}}';
            }
          }
        });
    });
});
</script>
@endsection


