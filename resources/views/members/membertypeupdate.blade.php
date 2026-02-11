@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Penentuan Jenis Keanggotaan</h3></div>
<form id="form-container" class="first-group">
      <div id="form"></div>
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

 
var membertype={!!$membertype!!};
var memtypeid="{!!$membertype->memtype_id!!}";
$("#form").dxForm({
    readOnly: false,
    formData:membertype,
    showColonAfterLabel: true,
    showValidationSummary: true,
    colCount: 1,
    items:[{
        itemType:"group",
        caption: "Jenis Keanggotaan",
        colCount:2,
        items: [{
            // dataField: "memtype_id",
            // label:{
            //     text:"ID Jenis Anggota",
            // },
            // editorOptions: { 
            //     value:"[AUTO NUMBER]",
            //     readOnly: true
            // }      
        // },{
            dataField: "memtype_desc",
            label:{
                text:"Nama Jenis Keanggotaan",
            },
            editorOptions: {
            },
        },{
            dataField: "memtype_min_value",
            label:{
                text:"Nilai Pencapaian",
            },
            editorOptions: {
                // value : 0,
                format: "#,##0",
            },            
        },]
    },{
        itemType:"group",
        caption: "Pengaturan Discount",
        colCount:2,
        items:[{
            dataField: "memtype_disc_state",
            label:{
                text:"Mendapatkan Discount",
            }, 
            editorType: "dxRadioGroup",
            editorOptions: {
                // value:"2",
                items: [{"memtype_disc_state":"1","memtype_disc_state_desc":"Ya"},
                        {"memtype_disc_state":"2","memtype_disc_state_desc":"Tidak"},],
                displayExpr: "memtype_disc_state_desc",
                valueExpr: "memtype_disc_state",
                layout: "horizontal",
            },
           
        },{
            dataField: "memtype_disc_type",
            label:{
                text:"Batas Mendapat Discount",
            }, 
            editorType: "dxRadioGroup",
            editorOptions: {
                // value:"2",
                items: [{"memtype_disc_type":"1","memtype_disc_type_desc":"Nominal"},
                        {"memtype_disc_type":"2","memtype_disc_type_desc":"Total Sales"},],
                displayExpr: "memtype_disc_type_desc",
                valueExpr: "memtype_disc_type",
                layout: "horizontal",
            }
        },{
            dataField: "memtype_disc_type_value",
            label:{
                text:"Batas Nominal Discount",
            },
            editorOptions: {
                // value : 0,
                format: "#,##0",
            },
        },{
            dataField: "memtype_disc_value",
            label:{
                text:"Discount Rupiah",
            },
            editorOptions: {
                // value : 0,
                format: "#,##0",
            },
        },{
            dataField: "memtype_disc_percent",
            label:{
                text:"Discount Persen",
            },
            editorOptions: {
                // value : 0,
                format: "#0.#%",
                step: 0.005
            },
        },]
    },{
        itemType:"group",
        caption: "Pengaturan Point",
        colCount:2,
        items:[
          {
            dataField: "memtype_point_state",
            label:{
                text:"Mendapatkan Point",
            }, 
          editorType: "dxRadioGroup",
          editorOptions: {
            // value:"2",
            items: [{"memtype_point_state":"1","memtype_point_state_desc":"Ya"},
                    {"memtype_point_state":"2","memtype_point_state_desc":"Tidak"},],
            displayExpr: "memtype_point_state_desc",
            valueExpr: "memtype_point_state",
            layout: "horizontal",
            }
        },{
            dataField: "memtype_point_value",
            label:{
                text:"Harga Point",
            },  
            editorOptions: {
                // value : 0,
                format: "#,##0",
            },
        },]
    },{
        itemType: "button",
        horizontalAlignment: "right",
        buttonOptions: {
            text: "Simpan",
            type: "success",
            onClick: function(e) {
            var form =$('#form-container').serializeObject();
            if(form['memtype_desc']==""){
                DevExpress.ui.notify({
                    message: "Silakan isi Nama Jenis Keanggotaan...",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
                return false;
            }
            if(form['memtype_min_value']=="0" || form['memtype_min_value']==""){
                DevExpress.ui.notify({
                    message: "Masukan Nilai Pencapaian Untuk Menjadi Anggota Jenis Ini...",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
                return false;
            }
            if(form['memtype_disc_type']=="1"){
                if(form['memtype_disc_type_value']=="0" || form['memtype_disc_type_value']==""){
                    DevExpress.ui.notify({
                    message: "Batas Nominal Discount Harus di Isi...",
                    position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 3000);
                    return false;
                }
            }
            if(form['memtype_disc_state']=="1"){
                if(form['memtype_disc_value']=="0" || form['memtype_disc_value']==""){
                    if(form['memtype_disc_percent']=="0" || form['memtype_disc_percent']==""){
                        DevExpress.ui.notify({
                        message: "Nominal atau prosentase discount Harus di Isi...",
                        position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "warning", 3000);
                        return false;
                    }
                }
            }
            if(form['memtype_point_state']=="1"){
                if(form['memtype_point_value']=="0" || form['memtype_point_value']==""){
                    DevExpress.ui.notify({
                    message: "Harga Point Harus di Isi...",
                    position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 3000);
                    return false;
                }
            }
            $.ajax({
              type: "PUT",
              url: "{{URL::to('agile/membertypes')}}"+"/"+memtypeid,
              data: JSON.stringify({form:form}),
              contentType: "application/json; charset=utf-8",
              dataType: "json",
              beforeSend: function()
              {
                  //do before send
              },
              success: function(response){
              },
              failure: function(errMsg) {
                  alert(errMsg);
              },
              complete: function(jqXHR) {
               if(jqXHR.readyState === 4) {
                   DevExpress.ui.notify({
                       message: "Data Jenis Keanggotaan Berhasil di Perbaharui",
                       position: {
                           my: "center top",
                           at: "center top"
                       }
                   }, "success", 3000);
                   window.location = "{{route('membertypes.index')}}";
                }
              }
            });
            }
        } 
    },]
});
});
</script>
@endsection
