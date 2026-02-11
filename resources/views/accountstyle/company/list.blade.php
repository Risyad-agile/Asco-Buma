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
                    return $("<div class='long-title'><h3>{!!$comp->comp_name!!} Account Style List</h3></div>");
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

    var gridDataSource = {!!$accstyles!!}
   
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
                    dataField: "company.comp_name",
                    caption: "Company",
                },{
                    dataField: "acc_style_comp_link",
                    caption: "Code",
                },{
                    dataField: "acc_style_comp_caption",
                    caption: "Caption",
                },{
                    dataField: "acc_style_comp_subtype",
                    caption: "Url",
                    visible:false,
                },{
                    dataField: "acc_style_comp_reference",
                    caption: "Report Name",
                    visible:false,
                },
            ],
            
    });
});
</script>
@endsection

