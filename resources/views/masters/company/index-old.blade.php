@extends('layouts.master')
@section('content')
<div class="long-title"><h3>Company List</h3></div>
<div id="gridContainer"></div> 
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var gridDataSource = new DevExpress.data.DataSource({
    load: function (key) {
        return $.ajax({
        url: "{{route('companies.create')}}",
        })
    }, 
    insert: function (values) {
        $.ajax({
            type: 'POST',
            url: '{{route('companies.store')}}',
            data: values,
            dataType: "json",
            success: function (data) {
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
                $("#gridContainer").dxDataGrid("instance").refresh();
                return false;
            },
            error: function(jqXHR, textStatus, errorThrown) {
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
        return false;
    },
    update: function(key, value) {
        var kunci= key.id;
        $.ajax({
            url: "{{URL::to('asri-core/companies')}}"+"/"+kunci,
            method: "PUT",
            data: value,
            dataType: "json",
            success: function (data) {
                if(data.code != 200) {
                swal({
                    title: data.status,
                    icon: data.status,
                    text: "Ada Kesalahan pada saat Update",
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                }
                else {
                swal({
                    title: data.status,
                    icon: data.status,
                    text: "Data berhasil diperbaharui",
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                }
            $("#gridContainer").dxDataGrid("instance").refresh();
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
            return false;
            }
        });
        return false;
    },
  });


  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        // keyExpr: "id",
        showBorders: true,
        columnHidingEnabled: true,
        editing: {
            mode: "popup",
            allowUpdating: true,
            allowAdding:true,
            useIcons: true,
            popup: {
                title: "Update Company",
                showTitle: true,
                position: {
                    my: "top",
                    at: "top",
                    of: window
                }
            }
        },
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 15
        },
        columns: [
            {
                dataField: "id",
                caption: "ID",
                value:"[AUTO NUMBER]",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "comp_name",
                caption: "Name",
                validationRules:
                [{
                    type: "required",
                    message: "Please Enter Company Name...",
                  }],
            },{
                dataField: "comp_address",
                caption: "Address ",
            },{
                dataField: "comp_city",
                caption: "City",
                visible:false,
            },{
                dataField: "comp_province",
                caption: "Province",
                visible:false,
            },{
                dataField: "comp_phone",
                caption: "Phone",
            },{
                dataField: "comp_email",
                caption: "Corporate Email",
                validationRules:
                [{
                    type: "required",
                    message: "Please Enter Company Name...",
                  }],
            },
        ]
    });
});
</script>
@endsection
