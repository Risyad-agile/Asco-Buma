@extends('layouts.master')
@section('content')
    <div id="toolbar"></div>
    <form method="POST" action="{{route('membertypes.main')}}">
        @csrf 
        <div id="tableMember"></div>
        <input id="txtMemberTypeId" type="text" name="memtype_id" class="form-control" hidden >
    </form>
@endsection

@section('script')
<script type="text/javascript">
$(function() {
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
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Daftar Jenis Keanggotaan</h3></div>");
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
  var membertypes = {!! $membertypes !!};
  
  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "memtype_id",
          load: function() {
              return jsonFile;
          }
      });
  };

  // table

  var dataGrid =$("#tableMember").dxDataGrid({
          dataSource: membertypes,
          keyExpr: "memtype_id",
          showBorders: true,
          allowColumnResizing: true,
          selection: {
            mode: "single"
          },
          hoverStateEnabled: true,
          searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
          paging: {
              pageSize: 7
          },
          columns: [
            {
                caption: "ID",
                dataField: "memtype_id",
                value:"[AUTO NUMBER]",
                visible:false,
            },{
                dataField: "memtype_desc",
                caption: "Jenis",
                // width:100,
            },{
                dataField: "memtype_min_value",
                caption: "Limit",
                dataType:"number",
                format: "fixedPoint",
                // width:100,
            },{
                dataField: "memtype_rule",
                caption: "Ketentuan",
            },
        ],
        toolbar: {
        items: [
            'searchPanel',
            {
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "plus",
                    hint: 'Buat Jenis Keanggotaan Baru',
                    useSubmitBehavior: true,
                    onClick: function(e) {      
                        var txtMemberTypeId=document.getElementById("txtMemberTypeId").value;
                        if(txtMemberTypeId!=""){
                            $("#txtMemberTypeId").val(""); //supaya ke server jadi null
                        }
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                  icon: "edit",
                  hint: 'Update Jenis Keanggotaan',
                  useSubmitBehavior: true,
                  onClick: function(e) {      
                    var txtMemberTypeId=document.getElementById("txtMemberTypeId").value;
                    if(txtMemberTypeId==""){
                        DevExpress.ui.notify({
                            message: "Silakan Pilih Jenis Keanggotaan..",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        e.preventDefault();
                        return false;
                    }
                   }
                }
            },
        ],},
          onSelectionChanged: function (selectedItems) {
            var data = selectedItems.selectedRowsData[0];
            $("#txtMemberTypeId").val(data.memtype_id);
           },
      });
   
});
</script>
@endsection
