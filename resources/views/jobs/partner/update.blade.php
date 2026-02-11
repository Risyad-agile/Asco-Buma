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
                    return $("<div class='long-title'><h3>Update Jobs</h3></div>");
                }

            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Keluar Tanpa Simpan', 
                    onClick: function(e) {      
                        window.location.href = "{{URL::to('partner/job/list/open')}}"+"/"+compid;
                    }
                }
            }]
    }); 
    const intervals = ['HOURLY','DAILY','WEEKLY','MONTHLY'];
    const dates=['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15',
        '16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31'];
    const days=['MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY','SUNDAY'];
    const connector={!!$connector!!};
    const job={!!$job!!};
    $("#form").dxForm({
        formData:job,
        colCount: 1,
        items:[{
          dataField: "job_name",
          label:{
            text:"Job Name",
          },
          editorOptions: { 
              placeholder : "Name of API Connection",
          }
        },{
          dataField: "connect_id",
          label:{
            text:"Connector",
          },
          editorType: 'dxSelectBox',
          editorOptions: {
            items: connector,
            displayExpr: 'connect_name',
            valueExpr: 'id',
          },
        },{
          dataField: "job_interval",
          label:{
            text:"Interval ",
          },
          editorType: "dxRadioGroup",
          editorOptions: { 
            items: intervals,  
            layout: 'horizontal',
          }
        },{
          dataField: "job_repeating_date",
          label:{
            text:"Repeat Date",
          }, 
          editorType: "dxSelectBox",
          editorOptions: { 
            items: dates,  
          }
        },{
          dataField: "job_repeating_day",
          label:{
            text:"Repeat Day",
          }, 
          editorType: "dxSelectBox",
          editorOptions: { 
            items: days,  
          }
        },{
          dataField: "job_execute_time",
          label:{
            text:"Execute Time ",
          },
          editorType: "dxDateBox",
          editorOptions: {
            type: 'time',
            showClearButton: true, 
            inputAttr: { 'aria-label': 'Clear Date' },
          }
        },{
            itemType: "button",
            horizontalAlignment: "right",
            buttonOptions: {
                text: "Update",
                type: "success", 
                onClick: function(e) {      
                    var form =$('#form-container').serializeObject();
                    if(form['job_name']==""){
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
                    saveJob(form)
                }
            }
      },]
  }); 
});
function saveJob(form) {
    const compid ="{!!$company->id!!}"; 
    const jobid="{!!$job->id!!}";
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
                url: "{{URL::to('partner/job')}}"+"/"+jobid,
                method: "PUT",
                data: JSON.stringify({compid,form}),                
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
                            title: "Succesful Send",
                            text: data.message,
                        }).then((result) => {
                          window.location.href = "{{URL::to('partner/job/list/open')}}"+"/"+compid;
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
