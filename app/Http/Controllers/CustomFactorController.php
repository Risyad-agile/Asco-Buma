<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomFactor; 
use App\Exports\CustomFactorExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class CustomFactorController extends Controller
{
    public function customFactorBrowse(Request $request)
    {
        $rawFactors = CustomFactor::with(['organization', 'factorSet'])->get();

        $customFactors = $rawFactors->map(function ($cf) {
            return [
                'id' => $cf->id,
                'organization_id' => $cf->organization_id,
                'factor_set_id' => $cf->factor_set_id,
                'source' => $cf->source,
                'reference' => $cf->reference,
                'category' => $cf->category,
                'subcategory' => $cf->subcategory,
                'name' => $cf->name,
                'associate_code' => $cf->associate_code,
                'factor_link' => $cf->factor_link,
                'data_type' => $cf->data_type,
                'sub_type' => $cf->sub_type,
                'unit' => $cf->unit,
                'factor_value' => $cf->factor_value,
                'co2' => $cf->co2,
                'ch4' => $cf->ch4,
                'n2o' => $cf->n2o,
                'biogenic' => $cf->biogenic,
                'co2e' => $cf->co2e,
                'calculation_method' => $cf->calculation_method,
                'description' => $cf->description,
                'effective_date' => $cf->effective_date,
                'published_date' => $cf->published_date,
                'country' => $cf->country,
                'state' => $cf->state,
                'city' => $cf->city,
                'sector' => $cf->sector,
                'scope' => $cf->scope,
                'is_active' => $cf->is_active,
                'organization' => [
                    'org_name' => optional($cf->organization)->org_name,
                ],
                'factor_set' => [
                    'name' => optional($cf->factorSet)->name,
                ],
            ];
        });

        $organizationId = $request->session()->get('organization_id');
        $factorset = $request->session()->get('factorset');

        return view('custom_factor/browse', [
            'customFactors' => $customFactors->toJson(),
            'organizationId' => $organizationId,
            'factorset' => $factorset,
            'title' => 'Custom Factor List',
            'subtitle' => 'Browse Custom Factors'
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:custom_factors,id',
            'country' => 'required|string|max:255',
            'factor_link' => 'nullable|string',
            'data_type' => 'nullable|string',
            'sub_type' => 'nullable|string',
            'factor_value' => 'nullable|numeric',
            'ch4' => 'nullable|numeric',
            'n2o' => 'nullable|numeric',
            'co2' => 'nullable|numeric',
            'biogenic' => 'nullable|numeric',
            'co2e' => 'nullable|numeric',
            'effective_date' => 'nullable|date',
            'effective_to' => 'nullable|date',
            'published_date' => 'nullable|date',
            'published_to' => 'nullable|date',
        ]);

        DB::table('custom_factors')->where('id', $request->id)->update($request->except('_token'));

        return redirect()->back()->with('success', 'Custom Factor updated successfully.');
    }


    public function edit($id)
    {
        $factor = CustomFactor::with(['organization', 'factorSet'])->findOrFail($id);
        return view('custom_Factor.edit', compact('factor'));
    }


    public function exportCustomFactor()
    {
        $timestamp = Carbon::now()->format('ymdHi');
        $filename = "Setup_Custom_Factors_{$timestamp}.xlsx";
        return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\CustomFactorMultiSheetExport, $filename );
        // return Excel::download(new CustomFactorExport, $filename);
    }
}
