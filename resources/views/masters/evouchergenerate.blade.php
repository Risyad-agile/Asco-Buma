@extends('layouts.master')
@section('content')
<div class="content">
  <body class="dx-viewport">
      <div class="long-title"><h3>Generate Eletronic Voucher</h3></div>
      <form id="form-container" class="first-group">
            <div id="form"></div>
     </form>
  </body>
</div>
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

$("#form").dxForm({
    readOnly: false,
    showColonAfterLabel: true,
    showValidationSummary: true,
    colCount: 1,
    items:[{
        itemType:"group",
        colCount:1,
        items: [{
            dataField: "evoucher_value_idx",
            label:{
                text:"Nominal Voucher (Rp.)",
            }, 
            editorType: "dxSelectBox",
            editorOptions: {
                value:"1",
                items: [{"evoucher_value_idx":"1","evoucher_value_idx_desc":"eVoucher 50.000"},
                        {"evoucher_value_idx":"2","evoucher_value_idx_desc":"eVoucher 100.000"},
                        {"evoucher_value_idx":"3","evoucher_value_idx_desc":"eVoucher 200.000"},
                        {"evoucher_value_idx":"4","evoucher_value_idx_desc":"eVoucher 500.000"},
                        {"evoucher_value_idx":"5","evoucher_value_idx_desc":"eVoucher 1.000.000"},],
                displayExpr: "evoucher_value_idx_desc",
                valueExpr: "evoucher_value_idx",
                layout: "horizontal",
            }
        },{
            dataField: "evoucher_qty",
            label:{
                text:"Jumlah Voucher ",
            },
            editorOptions: {
                value : 0,
                format: "#,##0",
            },
        },{
            dataField: "evoucher_point_value",
            label:{
                text:"Jumlah Poin Penukaran",
            },
            editorOptions: {
                value : 0,
                format: "#,##0",
            },  
        },{
          dataField: "evoucher_exp_date",
          label:{
            text:"Tanggal Expired",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              value : new Date(),
          }              
        },]
    },{
        itemType: "button",
        horizontalAlignment: "left",
        buttonOptions: {
            text: "Proses",
            type: "success",
            onClick: function(e) {
            var form =$('#form-container').serializeObject();
            if(form['evoucher_qty']=="0" || form['evoucher_qty']==""){
                DevExpress.ui.notify({
                    message: "Masukan Jumlah eVoucher yang akan di buat...",
                    position: {
                        my: "center top",
                        at: "center top"
                    }
                }, "warning", 3000);
                return false;
            }
            if(form['evoucher_point_value']=="0" || form['evoucher_point_value']==""){
                    DevExpress.ui.notify({
                    message: "Masukan Nilai Poin Penukaran...",
                    position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "warning", 3000);
                    return false;
            }

            var evoucherqty=form['evoucher_qty'];
            var result = DevExpress.ui.dialog.confirm("Proses Pembuatan Elektronik Voucher sebanyak "+evoucherqty+" unit, Lanjutkan...??", "Konfirmasi");
            result.done(function (dialogResult) {
                if(dialogResult){
                    $.ajax({
                        type: "POST",
                        url: "{{route('evouchers.store')}}",
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
                                message: "Data Jenis Keanggotaan Berhasil di Simpan",
                                position: {
                                    my: "center top",
                                    at: "center top"
                                }
                            }, "success", 3000);
                            window.location = "{{route('evouchers.index')}}";
                            }
                        }
                    });
                }
            });
 
 

            }
        } 
    },]
});
});
</script>
@endsection
