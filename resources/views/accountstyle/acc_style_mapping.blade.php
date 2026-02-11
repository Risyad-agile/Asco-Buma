@extends('layouts.master')

@section('content') 
    <div id="accstyle" style="margin-top: 10px;"></div>
@endsection

@section('script')
<script type="text/javascript">
    console.log("jQuery Loaded:", typeof jQuery !== "undefined" ? "Yes" : "No");
    console.log("DevExtreme Loaded:", typeof DevExpress !== "undefined" ? "Yes" : "No");
    $(function() {
        console.log("DevExtreme Loaded:", DevExpress);

        // Setup CSRF Token (Jika diperlukan untuk request POST/PUT/DELETE)
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // Data Source dengan error handling
        var gridDataSource = new DevExpress.data.DataSource({
            store: new DevExpress.data.CustomStore({
                key: "id",
                load: function(loadOptions) { 
                    return $.getJSON("{{ URL::to('partner/accountstyles/mapping') }}");
                } 
            })
        });

        // DataGrid DevExtreme
        const dataGrid = $("#accstyle").dxDataGrid({
            dataSource: gridDataSource,
            showBorders: true,
            paging: { pageSize: 18 },
            pager: { showInfo: true },
            allowColumnResizing: true,
            filterRow: { visible: true }, // 🔍 Filter di setiap kolom
            headerFilter: { visible: true }, // 🔽 Filter dropdown per kolom
            editing: {
                mode: "batch",
                allowUpdating: true,
                allowAdding: true,
                allowDeleting: true,
                selectTextOnEditStart: true,
                startEditAction: "click",
                useIcons: true,
            },
            toolbar: {
                items: [
                    {
                        location: "center",
                        template: function () {
                            return $("<div>")
                                .text("Account Type Mapping")
                                .css({
                                    "font-size": "22px",
                                    "font-weight": "bold",
                                    "font-family": "Arial, sans-serif",
                                    "margin-right": "10px"
                                });
                        }
                    },
                    "addRowButton", 
                    "revertButton", 
                    "saveButton",
                    {
                        widget: "dxButton",
                        location: "after",
                        options: {
                            icon: "close",
                            hint: "Close",
                            onClick: function () {
                                window.location.href = "{{ route('home') }}";
                            }
                        }
                    }
                ]
            },
            columns: [
                { dataField: "id", caption: "ID", visible: false, allowEditing: false },
                { dataField: "comp_id", caption: "Comp ID", visible: false, allowEditing: false },
                { dataField: "acc_style_comp_caption", caption: "Account Style" },
                { dataField: "acc_style_envz_caption", caption: "Account Style Envizi" } 
            ],
            onInitNewRow: function(e) { 
                e.data = { 
                    acc_style_comp_caption: "",
                    acc_style_envz_caption: ""
                };
            }
        }).dxDataGrid("instance");
    });
</script>
@endsection
