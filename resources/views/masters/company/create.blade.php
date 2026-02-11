@extends('layouts.master')

@section('content')
<div class="container-fluid px-4">
    <div class="card shadow-sm rounded-3 p-4">
        <h4 class="mb-4">Add New Company</h4>

        <div id="alertMsg"></div>

        <form id="companyForm" enctype="multipart/form-data">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="comp_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="comp_name" name="comp_name" required>
                </div>
                <div class="col-md-6">
                    <label for="comp_state" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="comp_state" name="comp_state" required>
                        <option value="1" selected>Active</option>
                        <option value="0">Non Active</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="comp_address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="comp_address" name="comp_address">
                </div>
                <div class="col-md-6">
                    <label for="comp_pos_code" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" id="comp_pos_code" name="comp_pos_code">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="comp_city" class="form-label">City</label>
                    <input type="text" class="form-control" id="comp_city" name="comp_city">
                </div>
                <div class="col-md-4">
                    <label for="comp_province" class="form-label">Province</label>
                    <input type="text" class="form-control" id="comp_province" name="comp_province">
                </div>
                <div class="col-md-4">
                    <label for="comp_phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="comp_phone" name="comp_phone">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="comp_email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="comp_email" name="comp_email" required>
                </div>
                <div class="col-md-6">
                    <label for="comp_logo" class="form-label">Logo</label>
                    <input type="file" class="form-control" id="comp_logo" name="comp_logo">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="db_host" class="form-label">DB Host</label>
                    <input type="text" class="form-control" id="db_host" name="db_host" value="localhost">
                </div>
                <div class="col-md-4">
                    <label for="db_name" class="form-label">DB Name</label>
                    <input type="text" class="form-control" id="db_name" name="db_name">
                </div>
                <div class="col-md-4">
                    <label for="db_user" class="form-label">DB User</label>
                    <input type="text" class="form-control" id="db_user" name="db_user">
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="db_pass" class="form-label">DB Password</label>
                    <input type="password" class="form-control" id="db_pass" name="db_pass">
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('companies.index') }}" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-success">Save Company</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
$(function() {
    $('#companyForm').on('submit', function(e) {
        e.preventDefault();

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('companies.store') }}",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.btn-success').attr('disabled', true).text('Saving...');
            },
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Company successfully created!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = "{{ route('companies.index') }}";
                });
            },
            error: function(xhr) {
                let errMsg = 'System error, please try again';
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    errMsg = Object.values(errors).map(e => e[0]).join('<br>');
                }
                $('#alertMsg').html(`<div class="alert alert-danger">${errMsg}</div>`);
            },
            complete: function() {
                $('.btn-success').attr('disabled', false).text('Save Company');
            }
        });
    });
});
</script>
@endsection
