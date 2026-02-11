@extends('layouts.master')
@section('content')

<form id="formTarget" method="POST">
    @csrf
    <div class="card mt-3 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5>Create Target Connector</h5>
        </div>

        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Protocol Name</label>
                    <input type="text" class="form-control" name="conn_target_name" id="conn_target_name" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Protocol Type</label>
                    <select class="form-select" name="conn_target_type" id="conn_target_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="S3" selected>AWS S3</option>
                        <option value="FTP">FTP</option>
                        <option value="LOCAL">Local</option>
                        <option value="AZURE">Azure</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Target Folder / Path</label>
                    <input type="text" class="form-control" 
                        name="conn_target_folder" 
                        value="{{ old('conn_target_folder', $target->conn_target_folder ?? '') }}" 
                        placeholder="e.g. envizi/reports/ or /mnt/data/exports/">
                </div> 
            </div>

            <hr class="my-4">
            <div id="configSection"></div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('connector_target.browse') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" id="btnSaveTarget" class="btn btn-primary">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>
</form>

@endsection

@section('script')
<script>
$(function(){

    function renderConfig(type){
        let html = "";

        if(type === "S3"){
            html = `
                <h5 class="mb-3 text-primary">S3 Configuration</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Bucket Name</label>
                        <input type="text" name="bucket" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Region</label>
                        <input type="text" name="region" class="form-control" placeholder="ap-southeast-1">
                    </div>
                    <div class="col-md-6">
                        <label>Access Key ID</label>
                        <input type="text" name="access_key" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Secret Key</label>
                        <input type="password" name="secret_key" class="form-control">
                    </div>
                </div>`;
        }
        else if(type === "FTP"){
            html = `
                <h5 class="mb-3 text-primary">FTP Configuration</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Host</label>
                        <input type="text" name="ftp_host" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>Port</label>
                        <input type="number" name="ftp_port" class="form-control" value="21">
                    </div>
                    <div class="col-md-3">
                        <label>Path</label>
                        <input type="text" name="ftp_path" class="form-control" placeholder="/upload/">
                    </div>
                </div>`;
        }
        else if(type === "LOCAL"){
            html = `
                <h5 class="mb-3 text-primary">Local Folder Configuration</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label>Folder Path</label>
                        <input type="text" name="local_path" class="form-control" placeholder="/mnt/data/export/">
                    </div>
                </div>`;
        }
        else if(type === "AZURE"){
            html = `
                <h5 class="mb-3 text-primary">Azure Blob Storage Configuration</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Account Name</label>
                        <input type="text" name="account" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Container Name</label>
                        <input type="text" name="container" class="form-control">
                    </div>
                </div>`;
        }
        else {
            html = `<h6 class="text-muted">Select Target Type to configure details...</h6>`;
        }

        $("#configSection").html(html);
    }

    // render default type "S3" saat halaman pertama kali load
    const defaultType = $("#conn_target_type").val() || "S3";
    $("#conn_target_type").val(defaultType);
    renderConfig(defaultType);

    // render ulang jika user ubah dropdown
    $("#conn_target_type").on("change", function(){
        renderConfig($(this).val());
    });

    $("#btnSaveTarget").on("click", function(){
        let data = $("#formTarget").serializeArray();
        let config = {};

        // ambil config tambahan & ubah ke JSON
        $("#configSection input").each(function(){
            config[$(this).attr("name")] = $(this).val();
        });

        data.push({ name: "config_json", value: JSON.stringify(config) });

        $.post("{{ route('connector_target.store') }}", data)
        .done(() => {
            Swal.fire("Success", "Target Connector saved!", "success")
                .then(() => window.location = "{{ route('connector_target.browse') }}");
        })
        .fail(() => {
            Swal.fire("Error", "Failed to save Target Connector", "error");
        });
    });
});
</script>
@endsection
