@extends('layouts.master')
@section('content')

<form id="formConnector" method="POST">
    @csrf
    <input type="hidden" id="comp_id" name="comp_id" value="{{ request('comp_id') }}">

    <div class="card mt-3 shadow-sm">
        <div class="card-header bg-primary text-white py-2">
            <h5 class="mb-0">Create New Source Connector</h5>
        </div>

        <div class="card-body py-3">

            {{-- Header Section --}}
            <div class="row g-3 align-items-end mb-2">
                <div class="col-md-6">
                    <label class="form-label mb-1">Connection Name</label>
                    <input type="text" class="form-control" name="connect_name" id="connect_name" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Connection Type</label>
                    <select class="form-select" name="connect_type" id="connect_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="API_KEY" selected>API Key</option>
                        <option value="BEARER_TOKEN">Bearer Token</option>
                        <option value="FTP">FTP</option>
                        <option value="DB">Database</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Location Data (Table)</label>
                    <input type="text" class="form-control" name="local_table" id="local_table" placeholder="Enter local table name">
                </div>
 
            </div>

            {{-- Source Config Section --}}
            <div id="configSection" class="mt-2"></div>

            {{-- Destination Config Section --}}
            <div id="destinationSection" class="mt-3">
                <h5 class="mb-3 text-primary">Destination Configuration</h5>

                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label class="form-label mb-1">Destination Target</label>
                        <select name="conn_target_id" id="conn_target_id" class="form-select" required>
                            <option value="">-- Select Target --</option>
                            @foreach($targets as $t)
                                <option value="{{ $t->id }}" 
                                    data-type="{{ $t->conn_target_type }}"
                                    data-config='@json(json_decode($t->config_json, true))' 
                                    {{ $t->is_default ? 'selected' : '' }}>
                                    {{ $t->conn_target_name }} ({{ $t->conn_target_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="destConfig" class="mt-2"></div>
            </div>

        </div>

        <div class="card-footer text-end py-2">
            <a href="{{ route('connector_source.browse') }}" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" id="btnSaveConnector" class="btn btn-primary btn-sm">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>
</form>

@endsection

@section('script')
<script>
$(function(){

    // ---------- RENDER SOURCE CONFIG ----------
    const renderSourceConfig = (type) => {
        let html = "";

        if(type === "API_KEY"){
            html = `
            <h5 class="mb-3 text-primary">Source API (API Key)</h5>
            <div class="row g-3 mb-1">
                <div class="col-md-1">
                    <label>Method</label>
                    <select name="api_method" class="form-select">
                        <option>GET</option><option>POST</option><option>PUT</option><option>DELETE</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Base URL</label>
                    <input type="text" name="api_base_url" class="form-control" placeholder="https://api.example.com">
                </div>
                <div class="col-md-5">
                    <label>Endpoint</label>
                    <input type="text" name="api_endpoint" class="form-control" placeholder="/data/list">
                </div>
                <div class="col-md-6">
                    <label>API Key Name</label>
                    <input type="text" name="api_key_name" class="form-control" placeholder="e.g. x-api-key">
                </div>
                <div class="col-md-6">
                    <label>API Key Value</label>
                    <input type="text" name="api_key_value" class="form-control" placeholder="Enter API Key value">
                </div>
                <div class="col-md-12">
                    <label>Request Body (JSON)</label>
                    <textarea name="api_body" class="form-control" rows="4" placeholder='{"param": "value"}'></textarea>
                </div>
                <div class="col-md-12 text-end mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTestAPI">
                        <i class="fa fa-plug"></i> Test Connection
                    </button>
                </div>
            </div>`;
        }
        else if(type === "BEARER_TOKEN"){
            html = `
            <h5 class="mb-3 text-primary">Source API (Bearer Token)</h5>
            <div class="row g-3 mb-1">
                <div class="col-md-1">
                    <label>Method</label>
                    <select name="api_method" class="form-select">
                        <option>GET</option><option>POST</option><option>PUT</option><option>DELETE</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Base URL</label>
                    <input type="text" name="api_base_url" class="form-control" placeholder="https://api.example.com">
                </div>
                <div class="col-md-5">
                    <label>Endpoint</label>
                    <input type="text" name="api_endpoint" class="form-control" placeholder="/data/list">
                </div>
                <div class="col-md-12">
                    <label>Bearer Token</label>
                    <input type="text" name="api_token" class="form-control" placeholder="Enter Bearer Token">
                </div>
                <div class="col-md-12">
                    <label>Request Body (JSON)</label>
                    <textarea name="api_body" class="form-control" rows="4" placeholder='{"param": "value"}'></textarea>
                </div>
                <div class="col-md-12 text-end mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnTestAPI">
                        <i class="fa fa-plug"></i> Test Connection
                    </button>
                </div>
            </div>`;
        }
        else if(type === "FTP"){
            html = `
            <h5 class="mb-3 text-primary">Source FTP Configuration</h5>
            <div class="row g-3 mb-1">
                <div class="col-md-4"><label>Host</label><input type="text" name="ftp_host" class="form-control"></div>
                <div class="col-md-2"><label>Port</label><input type="number" name="ftp_port" class="form-control" value="21"></div>
                <div class="col-md-3"><label>Username</label><input type="text" name="ftp_user" class="form-control"></div>
                <div class="col-md-3"><label>Password</label><input type="password" name="ftp_pass" class="form-control"></div>
                <div class="col-md-12"><label>Remote Path</label><input type="text" name="ftp_path" class="form-control" placeholder="/upload/"></div>
            </div>`;
        }
        else if(type === "DB"){
            html = `
            <h5 class="mb-3 text-primary">Source Database Configuration</h5>
            <div class="row g-3 mb-1">
                <div class="col-md-4"><label>DB Type</label>
                    <select name="db_type" class="form-select">
                        <option>MySQL</option><option>PostgreSQL</option><option>SQL Server</option><option>Oracle</option>
                    </select>
                </div>
                <div class="col-md-4"><label>Host</label><input type="text" name="db_host" class="form-control"></div>
                <div class="col-md-4"><label>Port</label><input type="number" name="db_port" class="form-control" value="3306"></div>
                <div class="col-md-6"><label>Database</label><input type="text" name="db_name" class="form-control"></div>
                <div class="col-md-3"><label>User</label><input type="text" name="db_user" class="form-control"></div>
                <div class="col-md-3"><label>Password</label><input type="password" name="db_pass" class="form-control"></div>
            </div>`;
        }
        else {
            html = `<h6 class="text-muted">Select connection type to configure details...</h6>`;
        }

        $("#configSection").html(html);
    };

    // ---------- Destination Config ----------
    const renderDestination = (type, cfg = {}) => {
        let html = "";
        switch ((type || '').toUpperCase()) {
            case "S3":
                html = `
                <div class="row g-3 mb-1">
                    <div class="col-md-6"><label>AWS Key ID</label>
                        <input type="text" name="aws_key_id" class="form-control" value="${cfg.aws_key_id || ''}">
                    </div>
                    <div class="col-md-6"><label>AWS Secret</label>
                        <input type="password" name="aws_key_secret" class="form-control" value="${cfg.aws_key_secret || ''}">
                    </div>
                    <div class="col-md-12"><label>Remote Folder</label>
                        <input type="text" name="aws_remote_folder" class="form-control" value="${cfg.aws_remote_folder || '/envizi/inbound/'}">
                    </div>
                </div>`;
                break;

            case "FTP":
                html = `
                <div class="row g-3 mb-1">
                    <div class="col-md-4"><label>Host</label><input type="text" name="ftp_host" class="form-control" value="${cfg.ftp_host || ''}"></div>
                    <div class="col-md-2"><label>Port</label><input type="number" name="ftp_port" class="form-control" value="${cfg.ftp_port || 21}"></div>
                    <div class="col-md-3"><label>User</label><input type="text" name="ftp_user" class="form-control" value="${cfg.ftp_user || ''}"></div>
                    <div class="col-md-3"><label>Password</label><input type="password" name="ftp_pass" class="form-control" value="${cfg.ftp_pass || ''}"></div>
                </div>`;
                break;

            default:
                html = `<div class="row g-3 mb-1">
                    <div class="col-md-12"><label>Target Folder Path</label>
                        <input type="text" name="target_path" class="form-control" value="${cfg.target_path || 'C:/data/export/'}">
                    </div>
                </div>`;
        }
        $("#destConfig").html(html);
    };

    // ---------- Events ----------
    $("#connect_type").on("change", function(){
        renderSourceConfig($(this).val());
    });

    $("#conn_target_id").on("change", function(){
        const sel = $("#conn_target_id option:selected");
        const type = sel.data("type") || "";
        let cfg = {};
        try { cfg = JSON.parse(sel.attr("data-config") || "{}"); } catch (e) { cfg = {}; }
        renderDestination(type, cfg);
    });

    // default render
    renderSourceConfig("API_KEY");
    const defaultOpt = $("#conn_target_id option:selected");
    let defaultCfg = {};
    try { defaultCfg = JSON.parse(defaultOpt.attr("data-config") || "{}"); } catch (e) { defaultCfg = {}; }
    renderDestination(defaultOpt.data("type") || "", defaultCfg);

    // ---------- Save handler ----------
    $("#btnSaveConnector").on("click", function(){
        const data = $("#formConnector").serialize();
        $.post("{{ route('connector_source.store') }}", data)
        .done(() => Swal.fire("Success", "Connector saved!", "success")
            .then(() => window.location = "{{ route('connector_source.browse') }}"))
        .fail(() => Swal.fire("Error", "Failed to save connector", "error"));
    });

    // ---------- Test Connection ----------
    $(document).on("click", "#btnTestAPI", function(){
        const base = $("[name='api_base_url']").val();
        const endpoint = $("[name='api_endpoint']").val();
        const method = $("[name='api_method']").val();
        const token = $("[name='api_token']").val();
        const apiKey = $("[name='api_key_name']").val();
        const apiVal = $("[name='api_key_value']").val();
        const body = $("[name='api_body']").val();

        if(!base || !endpoint){
            Swal.fire("Warning", "Please fill Base URL and Endpoint!", "warning");
            return;
        }

        Swal.fire({
            title: 'Testing Connection...',
            text: `${method} ${base}${endpoint}`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{ route('connector_source.testAPI') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                base_url: base,
                endpoint: endpoint,
                method: method,
                api_token: token,
                api_key_name: apiKey,
                api_key_value: apiVal,
                api_body: body
            },
            success: function(resp){
                Swal.close();
                if(resp.status === "success"){
                    Swal.fire({
                        icon: "success",
                        title: "Connection OK!",
                        html: `<pre style="text-align:left; font-size:12px;">${JSON.stringify(resp.data, null, 2)}</pre>`,
                        width: 600
                    });
                } else {
                    Swal.fire("Error", "Connection failed: " + resp.message, "error");
                }
            },
            error: function(err){
                Swal.close();
                Swal.fire("Error", "Test connection failed!", "error");
                console.error(err);
            }
        });
    });

});
</script>
@endsection
