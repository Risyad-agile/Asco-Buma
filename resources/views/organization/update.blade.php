@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <div class="dashboard-card">
        <h4 class="mb-4">Edit Organization</h4>

        <form id="formOrg" method="POST" action="{{ route('organizations.update',$organization->id) }}">
            @csrf 
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Company</label>
                    <div id="compBox"></div>
                    <input type="hidden" name="comp_id" id="comp_id" value="{{ $organization->comp_id }}">
                </div>

                <div class="col-md-8">
                    <label>Organization Name</label>
                    <div id="orgNameBox"></div>
                    <input type="hidden" name="org_name" id="org_name" value="{{ $organization->org_name }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label>Organization Link</label>
                    <div id="orgLinkBox"></div>
                    <input type="hidden" name="org_link" id="org_link" value="{{ $organization->org_link }}">
                </div>

                <div class="col-md-4">
                    <label>Status</label>
                    <div id="orgStateBox"></div>
                    <input type="hidden" name="org_state" id="org_state" value="{{ $organization->org_state }}">
                </div>
            </div>

            <div class="mt-4">
                <div id="btnUpdate"></div>
                <div id="btnCancel" class="ms-2"></div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
$(function(){

    $("#compBox").dxSelectBox({
        dataSource: @json($companies),
        displayExpr: "comp_name",
        valueExpr: "id",
        value: {{ $organization->comp_id }},
        stylingMode: "outlined",
        onValueChanged: e => $("#comp_id").val(e.value)
    });

    $("#orgNameBox").dxTextBox({
        value: @json($organization->org_name),
        stylingMode: "outlined",
        onValueChanged: e => $("#org_name").val(e.value)
    });

    $("#orgLinkBox").dxTextBox({
        value: @json($organization->org_link),
        stylingMode: "outlined",
        onValueChanged: e => $("#org_link").val(e.value)
    });

    $("#orgStateBox").dxSelectBox({
        dataSource: [
            {id:1,text:"Active"},
            {id:0,text:"Inactive"}
        ],
        displayExpr: "text",
        valueExpr: "id",
        value: {{ $organization->org_state }},
        stylingMode: "outlined",
        onValueChanged: e => $("#org_state").val(e.value)
    });

    $("#btnUpdate").dxButton({
        text: "Update",
        type: "success",
        icon: "save",
        onClick: function(){
            $("#formOrg").submit();
        }
    });

    $("#btnCancel").dxButton({
        text: "Cancel",
        icon: "close",
        onClick: function(){
            window.location.href = "{{ route('organizations.index') }}";
        }
    });

});
</script>
@endsection
