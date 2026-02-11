@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('partner.connector.main')}}">
    @csrf
    <div id="toolbar"></div>
    <div id="gridContainer"></div> 
    <input id="txtcompid" type="text" name="compid" value="{!!$company->id!!}" class="form-control" hidden>
    <input id="txtid" type="text" name="id" class="form-control" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" hidden >
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
    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
            template: function() {
                return $("<div class='long-title'><h3>Connector Configuration</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "plus",
                    hint: 'Create New Connector',
                    useSubmitBehavior: true,
                    onClick: function(e) {     
                        $("#txtstate").val("NEW"); 
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "edit",
                    hint: 'Edit Connector',
                    useSubmitBehavior: true,
                    onClick: function(e) {      
                        const txtid=document.getElementById("txtid").value;
                        $("#txtstate").val("UPDATE");  
                        if(txtid==""){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please Choose Connection...",
                              footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                          })
                          e.preventDefault();
                          return false;                       
                      }
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "trash",
                    hint: 'Delete Connector',
                    // useSubmitBehavior: true,
                    onClick: function(e) {    
                        const txtid=document.getElementById("txtid").value;  
                        $("#txtstate").val("DELETE");  
                        if(txtid==""){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please Choose Connection...",
                              footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                          })
                          e.preventDefault();
                          return false;                       
                      }
                    }
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
    var gridDataSource = {!!$connector!!};
    $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        keyExpr: "id",
        showBorders: true,
        searchPanel: {
            visible: true
        },
        selection: {
            mode: "single"
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                dataField: "id",
                caption: "ID",
                visible:false, 
            },{
                dataField: "connect_type",
                caption: "Type",
            },{
                dataField: "connect_name",
                caption: "Connection",
            },{
                dataField: "connect_protocol",
                caption: "Protocol",
            },
        ],
        onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            if(data) { 
                $("#txtid").val(data.id);  
            }
        },
    });
});
</script>
@endsection
