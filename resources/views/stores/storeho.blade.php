@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form id="form-container" class="first-group">
    <div id="form"></div>
    <div class="second-group">
        <div id="gridContainer"></div>
        <div id="btnSave" align="right"></div>
    </div>
</form>
@endsection

@section('script')
<script type="text/javascript">
$(function() {
  $.fn.serializeObject = function(){
      var o = {};
      var a = this.serializeArray();
      $.each(a, function() {
          if (o[this.name] !== undefined) {
              if (!o[this.name].push) {
                  o[this.name] = [o[this.name]];
              }
              o[this.name].push(this.value || '');
          } else {
              o[this.name] = this.value || '';
          }
      });
      return o;
  };

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
                    return $("<div class='long-title'><h3>Pengaturan Head Office (Kantor Pusat) Apotik</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: 'close',
                    hint: 'Tutup',
                    onClick() {
                        window.location = "{{route('home')}}";
                    },
                },
        
        }]
    });
  var companies = {!! $companies !!};
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('stores.ho.load')}}"
          })
      },
      insert: function (values) {
          return $.ajax({
              url: '{{route('stores.store')}}',
              method: "POST",
              data: values
          })
      },
      update: function (key, values) {
          var kunci= key.id;
          return $.ajax({
              url: "{{URL::to('zaida/stores')}}"+"/"+kunci,
              method: "PUT",
              data: values
          })
      }
  });
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,//prods,
        showBorders: true,
        editing: {
            mode: "popup",
            useIcons: true,
            allowUpdating: true,
            allowDeleting:true,
            // allowAdding:true,
            popup: {
                title: "Head Office",
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
            visible:false,
            editorOptions: {
                readOnly:true,
            },
        },{
            dataField: "comp_id",
            caption: "Perusahaan",
            validationRules:[{
                type: "required",
                message: "Pilih dari daftar",}],
            lookup: {
                  dataSource: companies,
                  valueExpr: "id",
                  displayExpr: "comp_brand",
            }
        },{
            dataField: "store_name",
            caption: "Nama ",
            validationRules: [{
                type: "required",
                message: "Harus di isi",
            },{
                type: "stringLength",
                max:50,
                message: "Maksimum 50 Karakter",
            }]
        },{
            dataField: "store_address",
            caption: "Alamat",
        },],
    });

});
</script>
@endsection