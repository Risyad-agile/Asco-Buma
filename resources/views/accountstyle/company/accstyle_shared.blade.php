@extends('layouts.master')
@section('content')
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
    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Envizi Account Style List</h3></div>");
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

    var gridDataSource = new DevExpress.data.DataSource({
        load: function (key) {
            return $.ajax({
                url: "{{route('accountstyles.shared.load')}}"
            })
        },    
    });
   
    $("#gridContainer").dxDataGrid({
            dataSource: gridDataSource,
            showBorders: true,
            searchPanel: {
                visible: true
            },
            paging: {
                pageSize: 10
            },
            columns: [{
                    dataField: "id",
                    caption: "ID",
                    visible:false,
                },{
                    dataField: "acc_style_link",
                    caption: "Link",
                    visible:false,
                },{
                    dataField: "acc_style_caption",
                    caption: "Caption",
                },{
                    dataField: "acc_style_scope",
                    caption: "Scope",
                },
            ],
    });
});


</script>
@endsection


