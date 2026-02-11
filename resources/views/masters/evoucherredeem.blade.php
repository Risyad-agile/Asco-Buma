@extends('layouts.master')
@section('content')
<div class="content">
        <body class="dx-viewport">
            <div class="long-title"><h3>Penukaran eVoucher dengan Member Point</h3></div>
            <div class="form-group row">
                <label for="memberno" class="col-sm-4 control-label text-md-left">Pilih Member</label>
                <div class="col-sm-8">
                    <div id="memberno"></div>
                </div>
            </div>
            <div class="form-group row">
                <label for="memberpoin" class="col-sm-4 control-label text-md-left">Jumlah Poin yang Dimiliki</label>
                <div class="col-sm-2">
                    <div id="memberpoin"></div>
                </div>
            </div>
            <div class="form-group row">
                <label for="evouchers" class="col-sm-4 control-label text-md-left">Pilih Vouchers</label>
                <div class="col-sm-8">
                    <div id="evouchers"></div>
                </div>
            </div>
            <div class="form-group row">
                <label for="evoucherpoin" class="col-sm-4 control-label text-md-left">Total Poin yang Diperlukan</label>
                <div class="col-sm-2">
                    <div id="evoucherpoin"></div>
                </div>
            </div>
            <div class="form-group row">
                <div id="btnSave"></div>
            </div>
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
var members = {!! $members !!};
var evouchers ={!!$evouchers!!};
var memberDataSource = function(jsonFile){
    return new DevExpress.data.CustomStore({
        loadMode: "raw",
        key: "member_no",
        load: function() {
            return jsonFile;
            }
        });
};
var evoucherDataSource = function(jsonFile){
    return new DevExpress.data.CustomStore({
        loadMode: "raw",
        key: "evoucher_no",
        load: function() {
            return jsonFile;
            }
        });
};
    
$("#memberno").dxDropDownBox({
    dataSource: memberDataSource(members),
    displayExpr: "member_name",
    valueExpr: "member_no",
    placeholder: "Select a value...",
    contentTemplate: function(e){
        var value = e.component.option("value"),
        $dataGrid = $("<div>").dxDataGrid({
            dataSource: e.component.option("dataSource"),
            columns: [{
                        caption: "ID Anggota",
                        dataField: "member_id",
                    },{
                        dataField: "member_no",
                        caption: "No Member",
                        visible:false,    
                    },{
                        dataField: "member_name",
                        caption: "Nama Anggota",
                    }],
            hoverStateEnabled: true,
            paging: { enabled: true, pageSize: 10 },
            filterRow: { visible: true },
            scrolling: { mode: "infinite" },
            height: 345,
            selection: { mode: "single" },
            selectedRowKeys: value,
            onSelectionChanged: function(selectedItems){
                var keys=selectedItems.selectedRowKeys;
                var memberpoint = selectedItems.selectedRowsData[0]['member_points'];
                $("#memberpoin").dxNumberBox('instance').option('value',memberpoint);
                $("#evoucherpoin").dxNumberBox('instance').option('value',0);
                e.component.option("value", keys);
                // console.log(keys);
            }
        });
        dataGrid = $dataGrid.dxDataGrid("instance");
        e.component.on("valueChanged", function(args){
            var value = args.value;
            dataGrid.selectRows(value, false);
        });
        return $dataGrid;
    }
});
$("#memberpoin").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    readOnly:true,
    rtlEnabled: true,
});
$("#evouchers").dxDropDownBox({
    dataSource: evoucherDataSource(evouchers),
    displayExpr: "evoucher_no",
    valueExpr: "evoucher_no",
    showClearButton: true,
    placeholder: "Select a value...",
    contentTemplate: function(e){
        var value = e.component.option("value"),
        $dataGrid = $("<div>").dxDataGrid({
            dataSource: e.component.option("dataSource"),
            columns: [{
                        caption: "NO Voucher",
                        dataField: "evoucher_no",
                    },{
                        dataField: "evoucher_exp_date",
                        caption: "Expired",
                        dataType: "date",
                        format: "dd-MM-yyyy",
                    },{
                        dataField: "evoucher_value_idx",
                        caption: "Jenis",
                        lookup: {
                            dataSource: [{"evoucher_value_idx":"1","evoucher_value_idx_desc":"eVoucher 50.000"},
                                {"evoucher_value_idx":"2","evoucher_value_idx_desc":"eVoucher 100.000"},
                                {"evoucher_value_idx":"3","evoucher_value_idx_desc":"eVoucher 200.000"},
                                {"evoucher_value_idx":"4","evoucher_value_idx_desc":"eVoucher 500.000"},
                                {"evoucher_value_idx":"5","evoucher_value_idx_desc":"eVoucher 1.000.000"},],
                            displayExpr: "evoucher_value_idx_desc",
                            valueExpr: "evoucher_value_idx",
                            layout: "horizontal",
                        }
                    }],
                    hoverStateEnabled: true,
            paging: { enabled: true, pageSize: 10 },
            filterRow: { visible: true },
            scrolling: { mode: "infinite" },
            height: 345,
            selection: { mode: "multiple" },
            selectedRowKeys: value,
            onSelectionChanged: function(selectedItems){
                var keys=selectedItems.selectedRowKeys;
                e.component.option("value", keys);
                var svouchers=$("#evouchers").dxDropDownBox('instance').option('value');
                var vpoin=$("#evoucherpoin").dxNumberBox('instance').option('value');

                var i,j,totpoin=0;
                for (i = 0; i < evouchers.length; i++) {
                    for(j=0;j<svouchers.length;j++){
                        if(svouchers[j]==evouchers[i]['evoucher_no']){                            
                            totpoin=totpoin+evouchers[i]['evoucher_value'];
                        }
                    }
                }
                $("#evoucherpoin").dxNumberBox('instance').option('value',totpoin);
            }
        });
        dataGrid = $dataGrid.dxDataGrid("instance");
        e.component.on("valueChanged", function(args){
            var value = args.value;
            dataGrid.selectRows(value, false);
        });
        return $dataGrid;
    }
});
$("#evoucherpoin").dxNumberBox({
    format: "#,##0.##",
    value: 0,
    readOnly:true,
    rtlEnabled: true,
});
$("#btnSave").dxButton({
      text: "Simpan",
      type: "success",
      width: 125,
      onClick: function(e) {
        var memberno=$("#memberno").dxDropDownBox('instance').option('value');
        var memberpoin=$("#memberpoin").dxNumberBox('instance').option('value');
        var svouchers=$("#evouchers").dxDropDownBox('instance').option('value');
        var vpoin=$("#evoucherpoin").dxNumberBox('instance').option('value');
        var i,j,totpoin=0;

        for (i = 0; i < evouchers.length; i++) {
            for(j=0;j<svouchers.length;j++){
                if(svouchers[j]==evouchers[i]['evoucher_no']){
                    
                    totpoin=totpoin+evouchers[i]['evoucher_value'];
                }
            }
        }
        if(memberno==null){
            DevExpress.ui.notify({
                message: "Silakan Pilih Anggota...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
        }
        if(evouchers==null){
            DevExpress.ui.notify({
                message: "Silakan pilih Voucher...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
        }
        if(vpoin>memberpoin){
            DevExpress.ui.notify({
                message: "Poin Penukaran tidak cukup...",
                position: {
                    my: "center top",
                    at: "center top"
                }
            }, "warning", 3000);
            return false;
        }
 
        var result = DevExpress.ui.dialog.confirm("Penukaran Voucher dengan Poin senilai "+vpoin+", Lanjutkan...??", "Konfirmasi");
        result.done(function (dialogResult) {
                if(dialogResult){
                    $.ajax({
                        type: "POST",
                        url: "{{route('evouchers.redeem.save')}}",
                        data: JSON.stringify({memberno:memberno[0],selectedvouchers:svouchers}),
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
                            window.location = "{{route('home')}}";
                            }
                        }
                    });
                }
            });
       }
  });


 
});
</script>
@endsection
