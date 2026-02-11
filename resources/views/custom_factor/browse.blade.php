@extends('layouts.master')
@section('content')
<form method="POST" action="{{route('customFactor.browse')}}">
    @csrf 

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div id="toolbarContainer" class="mb-3"></div>
                <div id="toolbarFilter" class="mb-3"></div>
                <div id="gridContainer" style="width: 100%;"></div>
            </div>
        </div>
    </div>

    <input id="txtid" type="text" name="taskid" class="form-control" value="1" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" value="LOAD" hidden>
    <input id="txttransno" type="text" name="transno" class="form-control" value="" hidden>
</form>

@include('custom_factor.update')

<style>
  /* Responsive DevExtreme toolbar */
  .dx-toolbar .dx-toolbar-items-container {
    flex-wrap: wrap;
  }

  /* Responsive search input */
  @media (max-width: 768px) {
    #toolbarFilter .dx-texteditor-input {
      width: 100% !important;
    }
  }

  /* Grid container size */
  #gridContainer {
    width: 100%;
    min-height: 400px;
  }
  #toolbarFilter {
    width: 100%;
  }

  #toolbarFilter .dx-texteditor {
    width: 100%;
  }

</style>

@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/exceljs/dist/exceljs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/file-saver/dist/FileSaver.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dayjs/1.11.10/dayjs.min.js"></script>

<script type="text/javascript">
  $(function(){
      var selectedData = null;
      const getData = {!! $customFactors !!};
      let dataGridInstance = null;

      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });

      // Toolbar
      $("#toolbarContainer").dxToolbar({
        items: [
            {
                location: "before",
                template: function () {
                    return $("<div>").text("Custom Factor List").css({
                        "font-size": "25px",
                        "font-weight": "bold",
                        "font-family": "Arial, sans-serif",
                        "margin-right": "20px"
                    });
                }
            },{
                location: "after",
                widget: "dxButton",
                options: {
                  icon : "activefolder",
                  hint : "Open & Edit Custom Factor", 
                  disabled: true,
                  onClick: function(){
                        if (!selectedData) {
                            DevExpress.ui.notify("Please select a row to edit", "warning", 2000);
                            return;
                        }

                        // Isi form modal dari data
                        $("#edit_id").val(selectedData.id);
                        $("#edit_organization_id").val(selectedData.organization_id);
                        $("#edit_name").val(selectedData.name);
                        $("#edit_country").val(selectedData.country);
                        $("#edit_city").val(selectedData.city);
                        $("#edit_state").val(selectedData.state);
                        $("#edit_factor_set_id").val(selectedData.factor_set_id);
                        $("#edit_associate_code").val(selectedData.associate_code);
                        $("#edit_factor_link").val(selectedData.factor_link);
                        $("#edit_data_type").val(selectedData.data_type);
                        $("#edit_sub_type").val(selectedData.sub_type);
                        $("#edit_unit").val(selectedData.unit);
                        $("#edit_calculation_method").val(selectedData.calculation_method);
                        $("#edit_source").val(selectedData.source);
                        $("#edit_reference").val(selectedData.reference);
                        $("#edit_category").val(selectedData.category);
                        $("#edit_subcategory").val(selectedData.subcategory);
                        $("#edit_sector").val(selectedData.sector);
                        $("#edit_scope").val(selectedData.scope);
                        $("#edit_description").val(selectedData.description);
                        $("#edit_is_active").prop("checked", selectedData.is_active == 1);

                        // FORMAT NUMBER (Fixed-point, 8 decimal)
                        $("#edit_factor_value").val(parseFloat(selectedData.factor_value ?? 0).toFixed(8));
                        $("#edit_ch4").val(parseFloat(selectedData.ch4 ?? 0).toFixed(8));
                        $("#edit_n2o").val(parseFloat(selectedData.n2o ?? 0).toFixed(8));
                        $("#edit_co2").val(parseFloat(selectedData.co2 ?? 0).toFixed(8));
                        $("#edit_biogenic").val(parseFloat(selectedData.biogenic ?? 0).toFixed(8));
                        $("#edit_co2e").val(parseFloat(selectedData.co2e ?? 0).toFixed(8));

                        // TANGGAL
                        $("#edit_effective_date").val(selectedData.effective_date);
                        $("#edit_published_date").val(selectedData.published_date);

                        $("#editCustomFactorModal").modal("show");
                    }
                }
            },{
                location: "after",
                widget: "dxButton",
                options: {
                    icon: "export",
                    hint: "Export to Excel File", 
                    onClick: function () {
                        window.location.href = "{{ route('customFactor.export') }}";
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

      // Filter toolbar
    $("#toolbarFilter").html(`
        <div class="d-flex w-100">
            <div class="flex-grow-1">
            <div id="searchBoxContainer"></div>
            </div>
        </div>
        `);

        $("#searchBoxContainer").dxTextBox({
        placeholder: "Search Custom Factor...",
        onInput: function (e) {
            let searchValue = e.event.target.value.toLowerCase();
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(function () {
                if (!searchValue) {
                    dataGridInstance.clearFilter();
                } else {
                    dataGridInstance.filter([
                        ["associate_code", "contains", searchValue], "or",
                        ["factor_link", "contains", searchValue], "or",
                        ["data_type", "contains", searchValue], "or",
                        ["sub_type", "contains", searchValue], "or",
                        ["country", "contains", searchValue], "or",
                        ["state", "contains", searchValue], "or",
                        ["city", "contains", searchValue]
                    ]);
                }
            }, 300);
        }
    });


      // DataGrid
      $("#gridContainer").dxDataGrid({
        dataSource: getData,
        keyExpr: "id",
        showBorders: true,
        columnAutoWidth: true,
        columnHidingEnabled: true,
        allowColumnResizing: true,
        scrolling: { columnRenderingMode: "standard" },
        selection: { mode: "single" },
        paging: { pageSize: 18 },
        export: { enabled: false, allowExportSelectedData: false },
        onExporting: function(e) {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet("setup");

            DevExpress.excelExporter.exportDataGrid({
                component: e.component,
                worksheet: worksheet,
                autoFilterEnabled: true
            }).then(function() {
                const filename = "Setup_Custom_Factors_" + dayjs().format("YYMMDDHHmm") + ".xlsx";
                workbook.xlsx.writeBuffer().then(function(buffer) {
                    saveAs(new Blob([buffer], { type: "application/octet-stream" }), filename);
                });
            });

            e.cancel = true;
        },
        columns: [
            // visible=true
            { dataField: "id", caption: "ID", width: 50 },
            { dataField: "factor_link", caption: "Link", width: 80, allignment:"center" },
            { dataField: "state", caption: "Region", width: 250 },
            { dataField: "data_type", caption: "Data Type", width: 300 },
            { dataField: "factor_set.name",caption: "Factor Set",  width: 130 },
            { dataField: "sub_type", caption: "Sub Type", width: 280 },
            { dataField: "factor_value", caption: "Total CO₂e (kgCO₂e/unit)", width: 180, dataType: "number", format: { type: "fixedPoint", precision: 2  } },
            { dataField: "co2", caption: "CO₂(kgCO₂e/unit)", width: 180, dataType: "number", format: { type: "fixedPoint", precision: 2  } },
            // visible=false 
            { dataField: "associate_code", caption: "Associate Code", width: 150, visible: false },
            { dataField: "unit", caption: "Unit", width: 80, visible: false },
            { dataField: "calculation_method", caption: "Calculation Method", width: 150, visible: false },
            { dataField: "source", caption: "Source", width: 150, visible: false },
            { dataField: "reference", caption: "Reference", width: 150, visible: false },
            { dataField: "category", caption: "Category", width: 120, visible: false },
            { dataField: "subcategory", caption: "Subcategory", width: 120, visible: false },
            { dataField: "sector", caption: "Sector", width: 120, visible: false },
            { dataField: "scope", caption: "Scope", width: 100, visible: false },
            { dataField: "is_active", caption: "Is Active?", width: 80, dataType: 'boolean', alignment: 'center', visible: false },
            { dataField: "co2e", caption: "CO₂e", width: 120, dataType: "number", format: "#,##0.00000000", visible: false },
            { dataField: "biogenic", caption: "Biogenic", width: 120, dataType: "number", format: "#,##0.00000000", visible: false },
            { dataField: "ch4", caption: "CH₄", width: 120, dataType: "number", format: "#,##0.00000000", visible: false },
            { dataField: "n2o", caption: "N₂O", width: 120, dataType: "number", format: "#,##0.00000000", visible: false },
            { dataField: "effective_date", caption: "Effective Date", width: 120, dataType: "date", visible:false },
            { dataField: "published_date", caption: "Published Date", width: 120, dataType: "date", visible:false },
            { dataField: "state", caption: "State", width: 100, visible: false },
            { dataField: "city", caption: "City", width: 100, visible: false },
            { dataField: "description", caption: "Description", width: 250, visible: false },
            { dataField: "organization.org_name", caption: "Organization", width: 150, visible: false },
        ],
        onSelectionChanged: function(selectedItems) {
            selectedData = selectedItems.selectedRowsData[0];
            var data = selectedItems.selectedRowsData[0];
            var toolbar = $("#toolbarContainer").dxToolbar("instance");
            if (data) {
                $("#txtid").val(data.id ?? '');
                $("#txttransno").val(data.trans_no ?? '');
                toolbar.option("items[1].options.disabled", false);
            } else {
                $("#txtid").val('');
                $("#txttransno").val('');
                toolbar.option("items[1].options.disabled", true);
            }
        },
        onInitialized: function(e) {
            dataGridInstance = e.component;
        }
      });

      $("#gridContainer").css("marginBottom", "50px");
  });
</script>
@endsection
