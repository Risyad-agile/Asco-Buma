@extends('layouts.master')

@section('content')
<head>
    <!-- Menambahkan CSS DevExtreme -->
    <link href="https://cdn3.devexpress.com/jslib/22.2.3/css/dx.light.css" rel="stylesheet">

    <!-- Menambahkan SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
<form id="gridForm">
    <input id="txtid" type="text" name="taskid" class="form-control" placeholder="Upload Task ID" hidden>
    <input id="txtstate" type="text" name="state" class="form-control" placeholder="Upload Task ID" hidden>

    <div id="toolbar"></div> <!-- Tempat toolbar akan ditampilkan -->
    <div id="gridContainer">
        <table id="gridTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Organization</th>
                    <th>Location</th>
                    <th>Account Style Caption</th>
                    <th>Account Number</th>
                    <th>Account Reference</th>
                    <th>Account Supplier</th>
                    <th>Record Start YYYY-MM-DD</th>
                    <th>Record End YYYY-MM-DD</th>
                    <th>Quantity</th>
                    <th>Total Cost (incl. Tax)</th>
                    <th>Record Reference</th>
                    <th>Record Invoice Number</th>
                    <th>Record Data Quality</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="id[]" hidden /></td>
                    <td><input type="text" name="organization_name[]" /></td>
                    <td><input type="text" name="location_name[]" /></td>
                    <td><input type="text" name="acc_style_caption[]" /></td>
                    <td><input type="text" name="acc_number[]" /></td>
                    <td><input type="text" name="acc_reference[]" /></td>
                    <td><input type="text" name="acc_supplier[]" /></td>
                    <td><input type="date" name="record_date_start[]" /></td>
                    <td><input type="date" name="record_date_end[]" /></td>
                    <td><input type="number" name="acc_data_qty[]" /></td>
                    <td><input type="number" step="0.01" name="acc_data_tot_cost[]" /></td>
                    <td><input type="text" name="record_reference[]" /></td>
                    <td><input type="text" name="record_inv_no[]" /></td>
                    <td><input type="text" name="record_quality[]" /></td>
                    <td><button type="button" class="removeRow">Remove</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</form>

<!-- Modal untuk task type (misalnya untuk "Mapping Account Style") -->
<div id="mdlTaskType" class="modal" tabindex="-1">
    <!-- Konten modal, misalnya form atau informasi lainnya -->
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mapping Account Style</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Isi modal, form atau informasi lainnya -->
                <p>Form untuk Mapping Account Style</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Menambahkan JS DevExtreme -->
<script src="https://cdn3.devexpress.com/jslib/22.2.3/js/dx.all.js"></script>

<!-- Menambahkan SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi toolbar dengan tombol yang sesuai
        $("#toolbar").dxToolbar({
            items: [{
                location: 'center',
                locateInMenu: 'never',
                template: function() {
                    return $("<div class='long-title'><h3>Data Capture</h3></div>");
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "rowfield",
                    hint: 'Mapping Account Style',
                    useSubmitBehavior: true,
                    onClick: function(e) {  
                        $('#mdlTaskType').modal('show');
                        $("#txtstate").val("MAPPING");      
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "check",
                    hint: 'Submit and Update task',
                    useSubmitBehavior: true,
                    onClick: function(e) {     
                        if(notupdate != 0) {
                            Swal.fire({
                                icon: "error",
                                title: "Validation Error",
                                text: "Please map Account Style first before continue...",
                                footer: '<a href="https://agile.co.id">Why do I have this issue?</a>'
                            });
                            e.preventDefault();
                            return false;  
                        }   
                        $("#txtstate").val("SUBMIT"); 
                    }
                }
            },{
                location: 'after',
                widget: 'dxButton',
                locateInMenu: 'auto',
                options: {
                    icon: "close",
                    hint: 'Close',
                    onClick: function(e) {      
                        window.location = "#";
                    }
                }
            }]
        });

        // Handle the Enter key press for input fields
        $(document).on('keydown', 'input', function(e) {
            var currentInput = $(this);
            var currentRow = currentInput.closest('tr');
            var currentColumn = currentInput.closest('td').index();
            var totalColumns = currentRow.find('td').length - 1; // Exclude remove button column

            if (e.key === 'Enter') {
                if (currentColumn === totalColumns - 1) {
                    // If it's the last column, add a new row and focus on the first input of the new row
                    var newRow = '<tr>' +
                        '<td><input type="text" name="location[]" /></td>' +
                        '<td><input type="text" name="acc_style[]" /></td>' +
                        '<td><button type="button" class="removeRow">Remove</button></td>' +
                    '</tr>';
                    currentRow.after(newRow); // Insert new row after the current row
                    currentRow.next().find('input:first').focus(); // Focus on the first input of the new row
                } else {
                    // Move to the next input field in the same row
                    currentRow.find('td').eq(currentColumn + 1).find('input').focus();
                }

                // Prevent form submission on Enter
                e.preventDefault();
            }
        });

        // Event handler for removing a row
        $(document).on('click', '.removeRow', function() {
            $(this).closest('tr').remove();
        });

        // Handle form submission with AJAX
        $('#gridForm').submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: '/store-grid',
                method: 'POST',
                data: formData,
                success: function(response) {
                    alert('Data saved: ' + JSON.stringify(response));
                }
            });
        });
    });
</script>

@endsection
