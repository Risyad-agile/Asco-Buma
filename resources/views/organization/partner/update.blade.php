@extends('layouts.master')
@section('content')
<div class="loadpanel"></div>
<div class="row justify-content-center">
  <div class="card" style="margin-top: 20px;">
    <div id="toolbar"></div>
    <div class="card-body">
      <form id="form-container" class="first-group">
        <div id="form"></div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
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
    $("#toolbar").dxToolbar({
        items: [{
            location: 'center',
            locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Syncronize Organization</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Keluar Tanpa Simpan', 
                    onClick: function(e) {      
                        window.location = "{{route('home')}}";
                    }
                }
            }]
    });
    const org={!!$org!!};
    $("#form").dxForm({
      formData:org,
      colCount: 1,
      items:[{
          dataField: "org_link",
          label:{
            text:"Organization Link",
          },
          editorOptions: { 
              placeholder : "Organization Link",
          }
        },{
            dataField: "org_name",
          label:{
            text:"Organization",
          },
          editorOptions: { 
              placeholder : "Organization",
          }
        },{
          dataField: "org_city",
          label:{
            text:"City ",
          },
          editorOptions: {
          }      
        },{
          dataField: "org_state_province",
          label:{
            text:"Province",
          },
          editorOptions: {
            readOnly:true,
          }      
        },{
          dataField: "org_street_address",
          label:{
            text:"Address",
          },
          editorType: "dxTextArea",
            editorOptions: {
                height: 100,
                placeholder : "Address"
              }  
        },{
          itemType: "button",
          horizontalAlignment: "right",
          buttonOptions: {
                icon: 'refresh',
                text: "Syncronize",
                type: "success", 
                onClick: function(e) {      
                    loadPanel.show(); 
              }
          }
      },]
  }); 
  const compid="{!!$compid!!}";
  const loadPanel = $('.loadpanel').dxLoadPanel({
        shadingColor: 'rgba(0,0,0,0.4)',
        position: { of: '#employee' },
        visible: false,
        showIndicator: true,
        showPane: true,
        shading: true,
        hideOnOutsideClick: false,
        onShown() {
            syncronizeOrganization(loadPanel,compid);       
            },
            onHidden() {
                // showEmployeeInfo(employee);
            },
        }).dxLoadPanel('instance');
});

function syncronizeOrganization(loadPanel,compid) {
    $.ajax({
        url: "{{route('partner.organization.store')}}",
        method: "POST",
        data: {compid:compid},
        dataType: "json",
        success: function (data) {
            if(data.code != 200) {
                swal({
                    title: data.status,
                    icon: data.status,
                    text: data.message,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
                loadPanel.hide(); 
            }else {
                swal({
                    title: data.status,
                    icon: data.status,
                    text: data.message,
                    value: true,
                    visible: true,
                    className: "",
                    closeModal: true,
                });
            }
            loadPanel.hide(); 
            location.reload();
            return false;
        },
        error: function(jqXHR, textStatus, errorThrown) {
            swal({
                title: "Error",
                icon: "error",
                text: qXHR.responseText,
                value: true,
                visible: true,
                className: "",
                closeModal: true,
            });
            loadPanel.hide(); 
            return false;
        }
    });
}
</script>
@endsection
