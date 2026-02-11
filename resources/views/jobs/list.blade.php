@extends('layouts.master')
@section('content')
<div class="loadpanel"></div>
<form method="POST" action="{{route('job.main')}}">
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
                return $("<div class='long-title'><h3>List Of Jobs</h3></div>");
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
                       // $("#txtstate").val("NEW"); 
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
                    icon: "video",
                    hint: 'Execute Job',
                    onClick: function(e) {    
                        const txtid=document.getElementById("txtid").value;  
                        if(txtid==""){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please Choose Job To Execute...",
                              footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                          })
                          return false;                       
                        }
                        const jobid=document.getElementById("txtid").value; 
                        retrieveExecute(loadPanel,jobid);     
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
    var gridDataSource = {!!$jobs!!};
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
                dataField: "job_name",
                caption: "Name",
            },{
                dataField: "job_interval",
                caption: "Interval",
            },{
                dataField: "job_execute_time",
                caption: "Execute Time",
            },
        ],
        onSelectionChanged: function(selectedItems){
            var data = selectedItems.selectedRowsData[0];
            if(data) { 
                $("#txtid").val(data.id);  
            }
        },
    });
    const loadPanel = $('.loadpanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#execute' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        onShown() {  
            },
        onHidden() {
            // showEmployeeInfo(employee);
        },
    }).dxLoadPanel('instance');
});
function retrieveExecute(loadPanel,jobid) {
    Swal.fire({
        title: 'Confirmation',
        text: "Are you sure want to Execute these Job...?",
        icon: 'question',
        showCancelButton: true, 
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.value) {
            loadPanel.show(); 
            $.ajax({
                url: "{{route('job.execute')}}",
                method: "POST",
                data: {jobid},
                dataType: "json",
                success: function (data) {
                if(data.code != 200) {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }else {
                    swal({
                        title: data.status,
                        icon: data.status,
                        text: data.message,
                        value: true,
                        visible: true,
                        className: "",
                        closeModal: true,
                    });
                }
                $("#gridContainer").dxDataGrid("instance").refresh();
                loadPanel.hide(); 
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                swal({
                    title: "Error",
                    icon: "error",
                    text: qXHR.responseText,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                loadPanel.hide(); 
                return false;
            }
        });
        }
    }); 
}
</script>
@endsection
