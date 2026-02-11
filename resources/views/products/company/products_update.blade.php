@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<form id="form-container" class="first-group">
<div id="form" style="margin-top: 25px;"></div> 
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
            return $("<div class='long-title'><h3>Pembaharuan Produk</h3></div>");
        }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "save",
                hint: 'Simpan',
                onClick: function(e) {      
                    var form =$('#form-container').serializeObject();
                    $.ajax({
                        type: "POST",
                        url: "{{route('products.company.update.save')}}",
                        data: JSON.stringify({form:form,product_id:product_id}),
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
                            }else{
                                swal({
                                    title: "OK",
                                    icon: data.status,
                                    text: data.message,
                                    value: true,
                                    visible: true,
                                    className: "",
                                    closeModal: true,
                                })
                                .then((value) => {
                                    window.location = '{{route('products.company.index')}}';
                                });
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
                    return false;
                }
            }
        },{
            location: 'after',
            widget: 'dxButton',
            locateInMenu: 'auto',
            options: {
                icon: "close",
                hint: 'Keluar Tanpa Simpan',
                onClick: function(e) {      
                    window.location = "{{route('products.company.index')}}";
                }
            }
        }]
    });  
  var makeAsyncDataSource = function(jsonFile){
      return new DevExpress.data.CustomStore({
          loadMode: "raw",
          key: "promo_no",
          load: function() {
              return jsonFile;
          }
      });
  }; 
  const product={!!$product!!};
  const product_id={!!$product->id!!};
  $("#form").dxForm({
      formData: product,
      colCount: 1,
      items:[{
        dataField: "product_name",
            label:{
                text:"Produk ",
            },
            editorOptions: { 
                readOnly: true
            },
        },{
            dataField: "product_stock_state",
            label:{
                text:"Status Stok",
            },
            editorType: 'dxSelectBox',
            editorOptions: {
                items: [{"product_stock_state":"1","product_stock_state_desc":"Stock"},
                            {"product_stock_state":"0","product_stock_state_desc":"Non Stock"}],
                valueExpr: "product_stock_state",
                displayExpr: "product_stock_state_desc",
                value:'1',
                searchEnabled: true,
            },
        },{
            dataField: "product_sell_high_price",
            label:{
                text:"Harga Jual Terendah",
            },
            editorType: 'dxNumberBox',
            editorOptions: {
                dataType:"number",
                format: "#,##0",
                value: 0,
            },
        },{
            dataField: "product_sell_lower_price",
            label:{
                text:"Harga Jual Tertinggi",
            },
            editorType: 'dxNumberBox',
            editorOptions: {
                dataType:"number",
                format: "#,##0",
                value: 0,
            },
        },
    ]
  });
}); 
</script>
@endsection
