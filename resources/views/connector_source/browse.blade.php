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

  // Data dari controller
  var gridDataSource = @json($connector);
  var companies = @json($companies);
  var companySelectBox = null;

  // Toolbar
  $("#toolbar").dxToolbar({
      items: [
          {
              location: 'center',
              template: () => $("<div class='long-title'><h3>Source Connector Setting</h3></div>")
          },
          {
              location: 'after',
              widget: 'dxButton',
              options: {
                  icon: "plus",
                  hint: 'Create New Connector',
                  onClick: function() {
                      var compId = companySelectBox ? companySelectBox.option("value") : null;
                      if(!compId){
                          Swal.fire({
                              icon: "warning",
                              title: "Company Not Selected",
                              text: "Please choose a company first!"
                          });
                          return;
                      }
                      window.location = "{{ route('connector_source.create') }}?comp_id=" + compId;
                  }
              }
          },
          {
              location: 'after',
              widget: 'dxButton',
              options: {
                  icon: "edit",
                  hint: 'Edit Connector',
                  onClick: function() {
                      const txtid = $("#txtid").val();
                      if(!txtid){
                          Swal.fire({
                              icon: "error",
                              title: "Validation Error",
                              text: "Please Choose Connection..."
                          });
                          return;
                      }
                      window.location = "{{ url('asri-core/connector-source/edit') }}/" + txtid;
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
                          Swal.fire("Warning", "Please select a connector first.", "warning");
                          return;
                      }

                      const rowData = selected[0];
                      const id = rowData.id;

                      $.post("{{ route('connector_source.setStatus') }}", { 
                          id: id, 
                          _token: "{{ csrf_token() }}" 
                      })
                      .done((response) => {
                          const newStatus = response.new_status ? 1 : 0;
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
                  onClick: function() {
                      window.location = "{{ route('home') }}";
                  }
              }
          }
      ]
  });

  // DataGrid
  var grid = $("#gridContainer").dxDataGrid({
      dataSource: gridDataSource,
      keyExpr: "id",
      showBorders: true,
      searchPanel: { visible: true, width: 300, placeholder: "Search..." },
      selection: { mode: "single" },
      paging: { pageSize: 10 },
      columns: [
          { dataField: "id", caption: "ID", visible: false },
          { dataField: "comp_id", caption: "Company ID", visible: false },
          { dataField: "company.comp_name", caption: "Company Name", width: 150 },
          { dataField: "conn_source_name", caption: "Connection Name" },
          { dataField: "conn_source_type", caption: "Connection Type" },
          { 
              dataField: "is_active", 
              caption: "Status", 
              alignment: "center", 
              cellTemplate: function(container, options) {
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
      },
      onToolbarPreparing: function(e) {
          var dataGrid = e.component;
          e.toolbarOptions.items.unshift({
              location: "before",
              template: function() {
                  var $container = $("<div>").css({"display":"inline-block","margin-right":"10px"});
                  var defaultCompany = (companies.length > 0) ? companies[0] : null;

                  var selectBox = $("<div>").appendTo($container).dxSelectBox({
                      dataSource: companies,
                      displayExpr: "comp_name",
                      valueExpr: "comp_id",
                      placeholder: "Filter by Company...",
                      showClearButton: false,
                      width: 260,
                      value: defaultCompany ? defaultCompany.comp_id : null,
                      onValueChanged: function(ev) {
                          var v = ev.value;
                          if(v) {
                              dataGrid.filter(["comp_id", "=", v]);
                          } else {
                              dataGrid.clearFilter();
                          }
                      }
                  }).dxSelectBox("instance");

                  companySelectBox = selectBox;

                  if (defaultCompany) {
                      dataGrid.filter(["comp_id", "=", defaultCompany.comp_id]);
                  }

                  return $container;
              }
          });
      }
  }).dxDataGrid("instance");

});
</script>
@endsection
