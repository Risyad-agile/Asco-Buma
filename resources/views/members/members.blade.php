@extends('layouts.master')
@section('content')
<div id="toolbar"></div>
<div id="gridContainer"></div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection

@section('script')
<script type="text/javascript">
$(function(){
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
            return $("<div class='long-title'><h3>Data Keanggotaan</h3></div>");
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
  var gridDataSource = new DevExpress.data.DataSource({
      load: function (key) {
          return $.ajax({
              url: "{{route('members.create')}}"
          })
      },
      insert: function (values) {
        return $.ajax({
              url: "{{route('members.store')}}",
              method: "POST",
              data: values,
              complete: function(jqXHR) {
                if(jqXHR.statusText == "OK") {
                    DevExpress.ui.notify({
                        message: "Data Anggota Berhasil di Simpan",
                        position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "success", 3000);
                }else{
                    DevExpress.ui.notify({
                        message: "Terjadi Kesalahan ".concat('[MEMBER EXIST]'),
                        position: {
                            my: "center top",
                            at: "center top"
                        }
                    }, "error", 3000);
                }
              }
          });
      },
      update: function (key, values) {
          var kunci= key.member_no;
          return $.ajax({
                url: "{{URL::to('agile/members')}}"+"/"+kunci,
                method: "PUT",
                data: values,
                complete:function(jqXHR) {              
                    if(jqXHR.statusText == "OK") {
                        DevExpress.ui.notify({
                            message: "Pembaharuan data anggota berhasil dilakukan",
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "success", 3000);
                    }else{
                        DevExpress.ui.notify({
                            message: "Nomor Anggota Sudah Ada".concat(jqXHR.responseJSON.errors.member_id[0]),
                            position: {
                                my: "center top",
                                at: "center top"
                            }
                        }, "error", 3000);
                    }
                }
            });
      },
      remove: function(key) {
            var kunci= key.member_no;
            return $.ajax({
              url: "{{URL::to('agile/members')}}"+"/"+kunci,
              method: "DELETE",
              complete: function(jqXHR) {
                if(jqXHR.statusText == "OK") {
                   DevExpress.ui.notify({
                       message: "Data Anggota berhasil di hapus",
                       position: {
                           my: "center top",
                           at: "center top"
                       }
                   }, "success", 3000);
                }
                  
               if(jqXHR.readyState === 4) {
                   DevExpress.ui.notify({
                       message: "Data Anggota berhasil di hapus",
                    //    jqXHR.responseJSON
                       position: {
                           my: "center top",
                           at: "center top"
                       }
                   }, "success", 3000);
                //    location.reload();
                }
              }
          })
      } 
  });

  function moveEditColumnToLeft(dataGrid) {
      dataGrid.columnOption("command:edit", {
          visibleIndex: -1
      });
  }
//   var sendRequest = function(value) {
//         var validEmail = "test@dx-email.com";
//         var d = $.Deferred();
//         setTimeout(function() {
//             d.resolve(value === validEmail);
//         }, 1000);
//         return d.promise();
//     }

  var membertypes={!!$membertypes!!};
  $("#gridContainer").dxDataGrid({
        dataSource: gridDataSource,
        // keyExpr: "member_id",
        showBorders: true,
        dateSerializationFormat: "dd-MM-yyyy",
        editing: {
            mode: "popup",
            allowUpdating: true,
            allowAdding:true,
            allowDeleting:true,
            useIcons: true,
            popup: {
                title: "Update Keanggotaan",
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
            pageSize: 10
        },
        columns: [
            {
                dataField: "member_no",
                caption: "No Anggota",
                value:"[AUTO NUMBER]",
                visible:false,
                editorOptions: {
                    readOnly:true,
                },
            },{
                dataField: "membertypes.memtype_id",
                caption: "Jenis Member",
                // width: 125,
                visible:false,
                lookup: {
                    dataSource: membertypes,
                    displayExpr: "memtype_desc",
                    valueExpr: "memtype_id",
                },
                validationRules: [{
                        type: "required",
                        message: "Harus di isi",
                }],
            },{
                dataField: "member_id",
                caption: "ID (Mobile No)",
                validationRules: [{
                  type: "required",
                  message: "Harus di isi",
                },{
                  type: "stringLength",
                  max:20,
                  message: "Maksimum 20 Karakter",
                }]
            },{
                dataField: "member_card_no",
                caption: "Nomor Kartu",
                visible:false,
                validationRules: [{
                  type: "required",
                  message: "Harus di isi",
                },{
                  type: "stringLength",
                  max:20,
                  message: "Maksimum 20 Karakter",
                }]    
            },{
                dataField: "member_name",
                caption: "Nama",
                validationRules: [{
                        type: "required",
                        message: "Harus di isi",
                }],
            },{
                dataField: "member_email",
                caption: "Alamat email",
                validationRules: [{
                    type: "required",
                    message: "Email is required"
                }, {
                    type: "email",
                    message: "Email is invalid"
                // }, {
                //     type: "async",
                //     message: "Email is already registered",
                //     validationCallback: function(params) {
                //         return sendRequest(params.value);
                //     }
                }],
            },{
                dataField: "member_ktp",
                caption: "Nomor KTP",
                visible:false,
                validationRules: [{
                  type: "stringLength",
                  max:20,
                  message: "Maksimum 20 Karakter",
                }]
            },{
                dataField: "member_pin",
                caption: "PIN",
                visible:false,
                validationRules: [{
                    type: "required",
                    message: "Harus di isi",
                },{                  
                    type: "stringLength",
                    min:6,
                    max:6,
                    message: "6 Digit", 
                }, {
                    type: "pattern",
                    pattern: /^\s*\d{6}$/,
                    message: "Harus Angka"
                }],
            },{
                dataField: "member_birth_place",
                caption: "Tempat Lahir",
                visible:false,
            },{
                dataField: "member_birth_date",
                caption: "Tanggal Lahir",
                visible:false,
                dataType: "date",
                format: "dd-MM-yyyy",
                validationRules: [{
                    type: "required",
                    message: "Harus di isi",
                }],
                showEditorAlways: true,
                editorOptions: {
                    invalidDateMessage: "Format Tanggal: dd-MM-yyyy"
                },
            },{
                dataField: "member_gender",
                caption: "Jenis Kelamin",
                visible:false,
                // width: 125,
                lookup: {
                    dataSource: ['Pria','Wanita'],
                },
                validationRules: [{
                        type: "required",
                        message: "Harus di isi",
                }],
            },
            {
                dataField: "member_religion",
                caption: "Agama",
                visible:false,
                // width: 125,
                lookup: {
                    dataSource: ['Islam','Protestan','Katolik','Hindu','Budha'],
                },
                validationRules: [{
                        type: "required",
                        message: "Harus di isi",
                }],
            },
            {
                dataField: "member_address",
                caption: "Alamat",
            },
        ],
        onContentReady: function (e) {
            moveEditColumnToLeft(e.component);
        },
    });
});
</script>
@endsection
