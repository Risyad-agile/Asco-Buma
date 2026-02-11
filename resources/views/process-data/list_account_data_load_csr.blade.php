@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form id="form-container" class="first-group">
    <div id="gridContainer"></div>
    <div id="btnSave" align="right"></div>
</form>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
      const accountdataloads = {!! $accountdataloads !!};
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
                return $("<div class='long-title'><h3>List Of Process CSR Account</h3></div>");
            }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Keluar Tanpa Simpan',
                    // useSubmitBehavior: true,
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            }]
        });
      $("#gridContainer").dxDataGrid({
          dataSource: accountdataloads,
          keyExpr: "id",
          showBorders: true,
          searchPanel: {
              visible: true,
              highlightCaseSensitive: true,
          },
          scrolling: {
            columnRenderingMode: 'virtual',
          },
          paging: {
            enabled: false,
          },
          columns: [{
                  dataField: "id",
                  caption: "ID",
                  visible:false,
              },{
                  dataField: "location_name",
                  caption: "Location",
              },{
                  dataField: "acc_style_caption",
                  caption: "Account Style",
              },{
                  dataField: "csr_male",
                  caption: "Male",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_female",
                  caption: "Female",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_less_30",
                  caption: "Age < 30",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_between_30_50",
                  caption: "30 > Age <50",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_more_50",
                  caption: "Age > 50",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_phd",
                  caption: "Education PHd",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_post_graduate",
                  caption: "Post Graduate",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_bachelor_degree",
                  caption: "Bachelor",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_high_school",
                  caption: "High School",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_junior_high_school",
                  caption: "Junior High School",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_elementary_school",
                  caption: "ELementary",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_eduction_other",
                  caption: "Education Other",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_islam",
                  caption: "Moslem",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_budha",
                  caption: "Budha",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_hindu",
                  caption: "Hindu",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_katolik",
                  caption: "Chatolic",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_kristen",
                  caption: "Christian",
                  dataType:"number",
                  format: "fixedPoint",
                },{
                  dataField: "csr_religion_other",
                  caption: "Other Religion",
                  dataType:"number",
                  format: "fixedPoint",
              },
          ],
      });
  });
</script>
@endsection
