@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('verify.task.crudE')}}">
    @csrf 
    <div id="toolbarContainer"></div> <!-- Toolbar baru di luar grid -->
    <div id="toolbarFilter"></div> 
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="taskid" class="form-control" placeholder="Upload Task ID" value = "1" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" placeholder="Upload Task Status" value = "LOAD" hidden>
    <input id="txttransno" type="text" name="transno" class="form-control" placeholder="Upload Trans No" value = "" hidden>
</form>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>
<script type="text/javascript">
  $(function(){
      var selectedData = null;
      const tasks = {!! $tasks !!};
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      
      $("#toolbarContainer").dxToolbar({
        items: [
            {
                location: "before",
                template: function () {
                    return $("<div>")
                        .text("Task Verification  - List")
                        .css({
                            "font-size": "25px",
                            "font-weight": "bold",
                            "font-family": "Arial, sans-serif",
                            "margin-right": "20px"
                        });
                }
            }, 
            {
                location : "after",
                widget:"dxButton",
                options: {
                  icon : "activefolder",
                  hint : "Task Verify",
                  useSubmitBehavior: true,
                  disabled:true,
                  onClick: function(){
                    const id = document.getElementById("txtid").value;  
                    const transno = document.getElementById("txttransno").value;
                    if (id == "") {
                        Swal.fire({
                            icon: "error",
                            title: "Validation Error",
                            text: "Please choose task",
                            footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                        });
                        e.preventDefault();
                        return false;
                    }
                    $("#txtid").val(id);
                    $("#txtstate").val("OPEN");   
                    $("#txttransno").val(transno);
                  }
                }
            },{
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "close",
                    hint: "Close Form",
                    onClick: function () {
                        window.location.href = "{{route('home')}}";
                    }
                }
            }
        ]
    });
    $("#toolbarFilter").dxToolbar({
      items: [
        {   
            location: "before",
            template: function () {
                return $("<span>")
                    .text("State by :")
                    .css({
                        "font-size": "20px",
                        "font-weight": "normal", 
                        "font-family": "Agency FB",
                        "margin-right": "10px"
                    });
            }
        },
        {
            location: "before",
            widget: "dxSelectBox",  // Harus pakai 'widget'
            options: {  // Semua konfigurasi masuk ke dalam 'options'
                dataSource: ["All", "Ready to Verify", "Ready to Approve", "Approved", "Rejected"], 
                value: "Ready to Verify",
                placeholder: "Filter by State",
                width : 200, 
                onValueChanged: function(e) {
                    var selectedState = e.value;
                    var dataGrid = $("#gridContainer").dxDataGrid("instance");

                    if (selectedState === "All") {
                        dataGrid.clearFilter();
                    } else {
                        dataGrid.filter(["task_state", "=", selectedState]);
                    }
                }
            }
        },{
            location: "after",
            widget: "dxTextBox",
            options: {
                placeholder: "Search Task...",
                width: window.innerWidth > 1000 ? 300 : "50%",
                onInput: function (e) {
                    let searchValue = e.event.target.value;
                    clearTimeout(window.searchTimeout);
                    window.searchTimeout = setTimeout(function() {
                        console.log("Filtering: ", searchValue);
                        let dataGrid = $("#gridContainer").dxDataGrid("instance");
                        if (!searchValue) {
                            dataGrid.clearFilter();
                        } else {
                            dataGrid.filter([
                                ["trans_no", "contains", searchValue], "or",
                                ["total_cost", "contains", searchValue] 
                            ]);
                        }
                    }, 300); // Delay 300ms biar gak nge-lag
                } 
            }
        }
      ]
    });

    $("#gridContainer").dxDataGrid({
        dataSource: tasks,
        keyExpr: "id",
        showBorders: true,
        columnAutoWidth:true, 
        scrolling: {
          columnRenderingMode: "standard"
        },
        allowColumnResizing: true,
        columnChooser: {
              enabled: false,
        },
        searchPanel: {
            visible: false,
            highlightCaseSensitive: true,
        },
        selection: {
          mode: "single"
        },
        paging: {
            pageSize: 18,
        },
        editing: {
              mode: "batch",
              allowUpdating: false,
              allowAdding:false,
              useIcons: true,
          },
        export: {
              enabled: false,
              fileName: "TaskList",
          },
        columns: [
              {
                dataField: "task_maker_time",
                caption: "Create Time",
                dataType: "datetime",
                width : 150,
                calculateCellValue: function (rowData) {
                    if (!rowData.task_maker_time) return "-"; // Handle jika null/undefined
                    return dayjs(rowData.task_maker_time).format("DD-MM-YYYY HH:mm");
                }
              },{
                dataField: "id",
                caption: "ID",
                alignment : "center",
                width : 50,  
                visible : false,
              },{
                dataField: "trans_no",
                caption: "No#",
                width : 130,
              },{
                dataField: "task_name",
                caption: "Task Name",
                width : 250,
              },{
                dataField: "task_maker_name",
                caption: "Create By",
                width: 100,
              },{
                dataField: "total_cost",
                caption: "Total Cost",
                width: 150,
                alignment: "right",
                dataType: "number",
                format: "#,##0", 
                calculateCellValue: function (rowData) {
                    return rowData.total_cost ? rowData.total_cost : 0;
                } 
              },{
                dataField: "input_methode",
                caption: "Input Methode",
                width : 200,
              },{
                dataField: "task_state",
                caption: "State", 
              }                   
        ],
        onSelectionChanged: function(selectedItems) {
            selectedData = selectedItems.selectedRowsData[0];
            var data = selectedItems.selectedRowsData[0];
            var toolbar = $("#toolbarContainer").dxToolbar("instance"); 

            if (data) {  
                $("#txtid").val(data.id); 
                $("#txtstate").val(data.task_state);  
                $("#txttransno").val(data.trans_no);
                toolbar.option(`items[1].options.disabled`, data.task_state !== "Ready to Verify"); // Open
                toolbar.option(`items[2].options.disabled`, data.task_state !== "Ready to Verify"); // Return Process 
                toolbar.option(`items[3].options.disabled`, data.task_state !== "Ready to Verify"); // Next Process 
            };
        },
        onInitialized: function(e) { 
          e.component.filter(["task_state", "=", "Ready to Verify"]); // ✅ Set default filter saat pertama kali load
        }
    });
    $("#gridContainer").css("marginBottorm","50px");
  });
</script>
@endsection
