@extends('layouts.master')
@section('content')

<form id="formTargetEdit" method="POST">
    @csrf
    <div class="card mt-3 shadow-sm">
        <div class="card-header bg-warning text-dark">
            <h5>Edit Target Connector</h5>
        </div>

        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Target Name</label>
                    <input type="text" class="form-control" name="conn_target_name" id="conn_target_name" 
                           value="{{ $target->conn_target_name }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Target Type</label>
                    <select class="form-select" name="conn_target_type" id="conn_target_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="S3" {{ $target->conn_target_type=='S3' ? 'selected' : '' }}>AWS S3</option>
                        <option value="FTP" {{ $target->conn_target_type=='FTP' ? 'selected' : '' }}>FTP</option>
                        <option value="LOCAL" {{ $target->conn_target_type=='LOCAL' ? 'selected' : '' }}>Local</option>
                        <option value="AZURE" {{ $target->conn_target_type=='AZURE' ? 'selected' : '' }}>Azure</option>
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
            <div id="configSection">
                <h5 class="text-primary mb-3">Configuration</h5>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('connector_target.browse') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" id="btnUpdateTarget" class="btn btn-warning text-dark">
                <i class="fa fa-save"></i> Update
            </button>
        </div>
    </div>
</form>

@endsection

@section('script')
<script>
$(function(){
    const type = "{{ $target->conn_target_type }}";
    const config = @json($config);

    function renderConfig(type, config = {}){
        let html = "";

        if(type === "S3"){
            html = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Bucket Name</label>
                        <input type="text" name="bucket" class="form-control" value="${config.bucket ?? ''}">
                    </div>
                    <div class="col-md-6">
                        <label>Region</label>
                        <input type="text" name="region" class="form-control" value="${config.region ?? ''}">
                    </div>
                    <div class="col-md-6">
                        <label>Access Key ID</label>
                        <input type="text" name="access_key" class="form-control" value="${config.access_key ?? ''}">
                    </div>
                    <div class="col-md-6">
                        <label>Secret Key</label>
                        <input type="password" name="secret_key" class="form-control" value="${config.secret_key ?? ''}">
                    </div>
                </div>`;
        }
        else if(type === "FTP"){
            html = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Host</label>
                        <input type="text" name="ftp_host" class="form-control" value="${config.ftp_host ?? ''}">
                    </div>
                    <div class="col-md-3">
                        <label>Port</label>
                        <input type="number" name="ftp_port" class="form-control" value="${config.ftp_port ?? '21'}">
                    </div>
                    <div class="col-md-3">
                        <label>Path</label>
                        <input type="text" name="ftp_path" class="form-control" value="${config.ftp_path ?? ''}">
                    </div>
                </div>`;
        }
        else if(type === "LOCAL"){
            html = `
                <div class="row g-3">
                    <div class="col-md-12">
                        <label>Folder Path</label>
                        <input type="text" name="local_path" class="form-control" value="${config.local_path ?? ''}">
                    </div>
                </div>`;
        }
        else if(type === "AZURE"){
            html = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Account Name</label>
                        <input type="text" name="account" class="form-control" value="${config.account ?? ''}">
                    </div>
                    <div class="col-md-6">
                        <label>Container Name</label>
                        <input type="text" name="container" class="form-control" value="${config.container ?? ''}">
                    </div>
                </div>`;
        }

        $("#configSection").html(html);
    }

    renderConfig(type, config);

    $("#conn_target_type").on("change", function(){
        renderConfig($(this).val());
    });

    $("#btnUpdateTarget").on("click", function(){
        let data = $("#formTargetEdit").serializeArray();
        let config = {};

        $("#configSection input").each(function(){
            config[$(this).attr("name")] = $(this).val();
        });

        data.push({ name: "config_json", value: JSON.stringify(config) });

        $.post("{{ route('connector_target.update', $target->id) }}", data)
        .done(() => {
            Swal.fire("Updated!", "Target Connector has been updated.", "success")
                .then(() => window.location = "{{ route('connector_target.browse') }}");
        })
        .fail(() => {
            Swal.fire("Error", "Failed to update Target Connector", "error");
        });
    });
});
</script>
@endsection
