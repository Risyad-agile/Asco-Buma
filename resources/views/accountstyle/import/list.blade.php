@extends('layouts.master')
@section('content')
<div class="loadpanel"></div>
<div id="toolbar"></div>
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
    const loadPanel = $('.loadpanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#gridContainer' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        message:"Please wait, data being process...",
        onShown() {
        //   setTimeout(() => {
        //     loadPanel.hide();
        //   }, 3000);
        },
        onHidden() {
        //   showEmployeeInfo(employee);
        },
    }).dxLoadPanel('instance');

    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
            template: function() {
                return $("<div class='long-title'><h3>Company Account Style List</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Close Syncronization',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            }]
    });
    var accstylesimport = {!!$accstylesimport!!};
   
    
  $("#gridContainer").dxDataGrid({
        dataSource: accstylesimport,
        showBorders: true,
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        columns: [
            {
                label: {
                    text: "ID",
                },
                dataField: "id",
                visible:false, 
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "acc_style_product",
                caption: "Product",
            },{
                dataField: "acc_style_datatype",
                caption: "Data Type",
            },{
                dataField: "acc_style_scope",
                caption: "Scope",
            },{
                dataField: "acc_style_caption",
                caption: "Caption", 
            },
        ],
        toolbar: {
            items: [
                'searchPanel',
                {
                    location: 'after',
                    widget: 'dxButton',
                    options: {
                        icon: 'save',
                        hint: 'Simpan',
                        useSubmitBehavior: true,
                        onClick() {
                            const state='SAVE';
                            saveImportProduct(accstylesimport,loadPanel,state); 
                        },
                    },
                },{
                    location: 'after',
                    widget: 'dxButton',
                    options: {
                        icon: 'close',
                        hint: 'Batalkan',
                        onClick() {
                            window.location = "{{route('home')}}";
                            const state='CANCEL';
                            var form =$('#form-container').serializeObject(); 
                        },
                    },
                },
            ],
        }, 
    });
});
function saveImportProduct(accstylesimport,loadPanel,state) {
    loadPanel.show();
    $.ajax({
        type: "POST",
        url: "{{route('accountstyles.import.save')}}",
        data: JSON.stringify({accstylesimport,state}),
        contentType: "application/json; charset=utf-8",
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
            loadPanel.hide();
            window.location = "{{route('home')}}";
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
</script>
@endsection

