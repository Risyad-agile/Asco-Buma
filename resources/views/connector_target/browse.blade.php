@extends('layouts.master')
@section('content')
<form method="POST"> 
    @csrf
    <div id="toolbar"></div>
    <div id="gridContainer"></div>
    <input id="txtid" type="text" name="id" hidden>
    <input id="txtstate" type="text" name="state" hidden>
</form> 
@endsection

@section('script')
<script type="text/javascript">
$(function(){
  $.ajaxSetup({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
  });

  var gridDataSource = @json($connector);

  $("#toolbar").dxToolbar({
      items: [
          {
              location: 'center',
              template: () => $("<div class='long-title'><h3>Destination Connector Setting</h3></div>")
          },
          {
              location: 'after',
              widget: 'dxButton',
              options: {
                  icon: "plus",
                  hint: 'Create New Target',
                  onClick: function() { 
                    window.location = "{{ route('connector_target.create') }}" ;
                  }
              }
          },
          {
              location: 'after',
              widget: 'dxButton',
              options: {
                  icon: "edit",
                  hint: 'Edit Target',
                  onClick: function() {
                      const txtid = $("#txtid").val();
                      if(txtid === ""){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please select Target Connector..."
                          });
                          return;
                      }
                      window.location = "{{ url('asri-core/connector_target/edit') }}/" + txtid;
                  }
              }
          },
          {
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "sorted",
                    hint: "Change Status",
                    onClick: function () {
                        const selected = grid.getSelectedRowsData();
                        if (selected.length === 0) {
                            Swal.fire("Warning", "Please select a target first.", "warning");
                            return;
                        }

                        const rowData = selected[0];
                        const id = rowData.id;

                        $.post("{{ route('connector_target.setStatus') }}", { 
                            id: id, 
                            _token: "{{ csrf_token() }}" 
                        })
                        .done((response) => {
                            const newStatus = response.new_status ? 1 : 0;

                            // update baris langsung tanpa refresh seluruh grid
                            const rowIndex = grid.getRowIndexByKey(id);
                            if (rowIndex >= 0) {
                                grid.cellValue(rowIndex, "is_active", newStatus);
                            }

                            Swal.fire("Success", "Status updated to: " + (newStatus ? "Active" : "Inactive"), "success");
                        })
                        .fail((xhr) => {
                            let msg = "Failed to update status.";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                msg = xhr.responseJSON.error;
                            }
                            Swal.fire("Error", msg, "error");
                        });
                    }
                }
        }, 
        {
            location: 'after',
            widget: 'dxButton',
            options: {
                icon: "close",
                hint: 'Close',
                onClick: () => window.location = "{{ route('home') }}"
            }
        }
      ]
  });

  var grid = $("#gridContainer").dxDataGrid({
      dataSource: gridDataSource,
      keyExpr: "id",
      showBorders: true,
      searchPanel: { visible: true, width: 300, placeholder: "Search..." },
      selection: { mode: "single" },
      paging: { pageSize: 10 },
      columns: [
          { dataField: "id", caption: "ID", width: 40 },
          { dataField: "conn_target_name", caption: "Protocol Name", width: 200 },
          { dataField: "conn_target_type", caption: "Pro-Type", width: 100 },
          { dataField: "bucket", caption: "Bucket/path" }, 
          { dataField: "AccessKey", caption: "Access Key" }, 
          { dataField: "folder", caption: "Folder/Path" },
          {
            dataField: "is_active",
            caption: "Status",
            alignment: "center",
            cellTemplate: function (container, options) {
                const icon = options.value ? "✅" : "❌";
                const color = options.value ? "green" : "red";
                $("<span>")
                    .text(icon)
                    .css("color", color)
                    .appendTo(container);
            }
         }
      ],
      onSelectionChanged: function(selectedItems){
          var data = selectedItems.selectedRowsData[0];
          if(data) $("#txtid").val(data.id);
      }
  }).dxDataGrid("instance");

});
</script>
@endsection
