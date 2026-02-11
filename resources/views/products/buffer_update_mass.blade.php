@extends('layouts.master')
@section('content')
<div class="row justify-content-center">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000" data-autohide="true" >
        <div class="toast-header">
            <strong class="me-auto">Informasi Produk Baru</strong>
            <small>Kidswa Farma</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Proses ini akan mengubah semua nilai Stok Minimum(Buffer Stok) sesuai nilai
            yang dimasukan
        </div>
    </div>
    <div class="col-md-8" style="margin-top: 25px;">
        <div class="card">
            <div id="toolbar"></div>
            <div class="card-body">
                <form method="POST" action="{{route('products.buffer.main')}}">
                    @csrf 
                    <div class="form-group row">
                        <label for="numBufferStock" class="col text-md-left">Masukan Nilai Stok Minimal</label>
                        <div class="col-sm-4" style="margin-bottom: 20px;">
                            <div id="numBufferStock"></div>
                        </div>
                        <div id="btnProses"></div>
                        <div class="loadpanel"></div>
                    </div>                    
                </form>
            </div>
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

    $("#numBufferStock").dxNumberBox({
        format: "#,##0.##",
        value: 0,  
        rtlEnabled: true,
    });
    $("#toolbar").dxToolbar({
    items: [{
        location: 'center',
        locateInMenu: 'never',
        template: function() {
            return $("<div class='long-title'><h3>Perbaharuan Massal</h3></div>");
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
    const storeid="{!!$storeid!!}";
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
        },
        onHidden() {
        },
    }).dxLoadPanel('instance');

    $("#btnProses").dxButton({
        type: "success",
        text: "Proses",
        // useSubmitBehavior: true,
        onClick: function(e) {                  
            var bufferstock=$("#numBufferStock").dxNumberBox('instance').option('value');
            $("#btnProses").dxButton("instance").option("disabled",true);
            Swal.fire({
                title: "Proses ini akan mengaktifkan semua produk di Apotek, lanjutkan...??",
                showCancelButton: true,
                confirmButtonText: 'Ya, Aktifkan', 
                cancelButtonText: 'Batal', 
            }).then((result) => {
                loadPanel.show();
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('products.buffer.mass.save')}}",
                        method: "POST",
                        data: JSON.stringify({storeid:storeid,bufferstock:bufferstock}),
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
                            }
                            window.location = "{{route('products.buffer.index')}}";
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
            });
        }
    });
});
</script>
@endsection


