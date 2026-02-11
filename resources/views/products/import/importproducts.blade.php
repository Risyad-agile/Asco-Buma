@extends('layouts.master')
@section('content')
    <div class="long-title"><h3>Impor Produk</h3></div>
    <div class="short-title"><h4>Impor Produk kedalam Sistem</h4></div>
    <div id="gridContainer"></div> 
    <div class="loadpanel"></div>
    <input id="txtCompId" type="text" name="compid" class="form-control" value="{!!$compid!!}" hidden>
    <input id="txtType" type="text" name="type" class="form-control" value="{!!$type!!}" hidden>

    {{-- add official memo desc dialog --}}
    <div class="modal fade" id="mdlMutationIn" role="dialog">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 id="mdlMutationInTitle" class="modal-title"><span class="badge badge-primary">Informasi Penerimaan Produk</span></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form-container" class="first-group">
            <div id="form"></div>
            <div class="box-body" style="padding-top: 5px;">
                  <div id="btnSave"></div>
            </div>
        </form>
      </div>
    </div>
    </div>
    </div>
    {{-- end of official memo desc dialog --}}
@endsection

@section('script')
<script type="text/javascript">
$(function(){
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
  const loadPanel = $('.loadpanel').dxLoadPanel({
    shadingColor: 'rgba(0,0,0,0.4)',
    position: { of: '#gridContainer' },
    visible: false,
    showIndicator: true,
    showPane: true,
    shading: true,
    hideOnOutsideClick: false,
    message:"Harap Menunggu, sedang proses...",
    onShown() {
    //   setTimeout(() => {
    //     loadPanel.hide();
    //   }, 3000);
    },
    onHidden() {
    //   showEmployeeInfo(employee);
    },
  }).dxLoadPanel('instance');

  var gridDataSource = {!! $productimports !!};
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,//prods,
        showBorders: true,
        allowColumnResizing: true,
        searchPanel: {
            visible: true
        },
        paging: {
            pageSize: 10
        },
        groupPanel: {
            visible: true
        },
        columns: [
            {
                dataField: "product_plu",
                caption: "PLU",
            },{
                dataField: "product_brand",
                caption: "Merek",
                visible:false,
            },{
                dataField: "product_category",
                caption: "Kategori Produk",
                visible:false,
            },{
                dataField: "product_name",
                caption: "Deskripsi",     
            },{
                dataField: "product_barcode",
                caption: "Barcode",
                visible:false,
            },{
                dataField: "product_buy_price",
                caption: "Harga Beli",  
                // width:125,
                // visible:false,
                dataType:"number",
                format: "fixedPoint",
                editorType: "dxNumberBox",
            },{
                dataField: "product_sell_price",
                caption: "Harga Jual",  
                // width:125,
                // visible:false,
                dataType:"number",
                format: "fixedPoint",
                editorType: "dxNumberBox",
            },{
                dataField: "product_stock",
                caption: "Stok",
                // width:125,
                // visible:false,
            },
        ],
        toolbar: {
        items: [
            'searchPanel',
            {
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'save',
                    hint: 'Simpan',
                    useSubmitBehavior: true,
                    onClick() {
                        const compid="{!! $compid !!}";
                        const state='SAVE';
                        const type="{!!$type!!}";
                        var form =$('#form-container').serializeObject();
                        if(type=="3"){
                            saveImportProduct(loadPanel,compid,state,type,form); 
                        }
                        if(type=="4"){
                            $('#mdlMutationIn').modal('show');
                        }
                    },
                },
            },{
                location: 'after',
                widget: 'dxButton',
                options: {
                    icon: 'close',
                    hint: 'Batalkan',
                    onClick() {
                        window.location = "{{route('home')}}";
                        window.location = "{{route('home')}}";
                        const compid="{!! $compid !!}";
                        const state='CANCEL';
                        var form =$('#form-container').serializeObject(); 
                    },
                },
            },
        ],}, 
    });
    $("#form").dxForm({
      colCount: 1,
      items:[
      {
        itemType:"group",
        colCount:2,
        items: [{
          dataField: "mutin_docno",
          label:{
            text:"No Dokumen",
          },
        },{
          dataField: "mutin_date",
          label:{
            text:"Tanggal Dokumen",
          },
          editorType: "dxDateBox",
          editorOptions: {
              displayFormat: "dd-MM-yyyy",
              value : new Date(),
          }
        },]
      },{
        itemType:"group",
        colCount:1,
        items:[
          {
            dataField: "mutin_note",
            label:{
              text:"Keterangan",
            },
            editorType: "dxTextArea",
            editorOptions: {
                height: 75,
                placeholder : "Keterangan",
                maxLength:100,
              }
          },]
      },{
        itemType: 'button',
        horizontalAlignment: 'right',
        buttonOptions: {
            text: 'Simpan',
            type: 'success',
            // useSubmitBehavior: true,
            onClick() {
                $('#mdlMutationIn').modal('hide');
                const compid="{!! $compid !!}";
                const state='SAVE';
                const type="{!!$type!!}";
                var form =$('#form-container').serializeObject();
                saveImportProduct(loadPanel,compid,state,type,form); 
            }
        },
      },
    ]
  });
});

function saveImportProduct(loadPanel,compid,state,type,form) {
    loadPanel.show();
    $.ajax({
        type: "POST",
        url: "{{route('product.import.save')}}",
        data: JSON.stringify({form,compid,type,state}),
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
            }
        else {
            swal({
                title: "OK",
                icon: data.status,
                text: data.message,
                value: true,
                visible: true,
                className: "",
                closeModal: true,
            });
            loadPanel.hide();
            window.location = "{{route('home')}}";
        }        
        return false;
    },
    error: function(jqXHR, textStatus, errorThrown) {
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
}
</script>
@endsection
