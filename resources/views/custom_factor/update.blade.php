<!-- Modal Edit Custom Factor -->
<div class="modal fade" id="editCustomFactorModal" tabindex="-1" aria-labelledby="editCustomFactorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="editCustomFactorForm" method="POST" action="{{ route('customFactor.update') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="editCustomFactorModalLabel">Edit Custom Factor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id"> 
          <!-- Region Field -->
          <div class="row mb-3"> 
            <div class="col-md-2"><label class="fw-bold text-decoration-underline" style="color:#ff0000;">REGION</label><input type="text" name="country" id="edit_country" class="form-control"></div> 
          </div>

          <!-- Factor Details -->
          <div class="row mb-0"><div class="col-md-2"><label class="fw-bold text-decoration-underline" style="color:#ff0000;">FACTOR DETAILS</label></div></div>
          <div class="row mb-3"> 
            <div class="col-md-2 "><label>Factor Link</label><input type="text" name="factor_link" id="edit_factor_link" class="form-control"></div> 
            <div class="col-md-5"><label>Data Type</label><input type="text" name="data_type" id="edit_data_type" class="form-control"></div>
            <div class="col-md-5"><label>Sub Type</label><input type="text" name="sub_type" id="edit_sub_type" class="form-control"></div>   
          </div>   

          <!-- Factor Value -->
          <div class="row mb-0"><div class="col-md-2"><label class="fw-bold text-decoration-underline" style="color:#ff0000;">FACTOR VALUES</label></div></div>
          <div class="row mb-3">
            <div class="col-md-2"><label>Total CO₂</label><input type="number" step="0.0001" name="factor_value" id="edit_factor_value" class="form-control"></div>
            <div class="col-md-2"><label>CH₄</label><input type="number" step="0.0001" name="ch4" id="edit_ch4" class="form-control"></div>
            <div class="col-md-2"><label>N₂O</label><input type="number" step="0.0001" name="n2o" id="edit_n2o" class="form-control"></div>
            <div class="col-md-2"><label>CO₂</label><input type="number" step="0.0001" name="co2" id="edit_co2" class="form-control"></div>
            <div class="col-md-2"><label>Biogenic</label><input type="number" step="0.00001" name="biogenic" id="edit_biogenic" class="form-control"></div>
            <div class="col-md-2"><label>CO₂e</label><input type="number" step="0.00001" name="co2e" id="edit_co2e" class="form-control"></div>
          </div>
 
          <!-- EFFECTIVE AND PUBLISHED DATE-->
          <div class="row mb-0"><div class="col-md-6"><label class="fw-bold text-decoration-underline" style="color:#ff0000;">EFFECTIVE AND PUBLISHED DATE</label></div></div>
          <div class="row mb-3">
            <div class="col-md-2"><label>Effective Date</label><input type="date" name="effective_date" id="edit_effective_date" class="form-control"></div>
            <div class="col-md-2"><label>Published Date</label><input type="date" name="published_date" id="edit_published_date" class="form-control"></div>
          </div>

           
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
