<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Companies;

class CompaniesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Ambil company id user
    private function compid(){
        return Auth::user()->organization->company->id;
    }

    // ======================
    // INDEX PAGE
    // ======================
    public function browse()
    {
        // Index blade nanti ambil data via AJAX
        return view('masters.company.browse');
    }

    // ======================
    // CREATE PAGE
    // ======================
    public function create()
    {
        // Form create
        return view('masters.company.create');
    }

    // ======================
    // STORE NEW COMPANY
    // ======================
    public function store(Request $request)
    {
        $request->validate([
            'comp_name' => 'required',
            'comp_email' => 'required|email',
            'comp_state' => 'required'
        ]);

        Companies::create($request->all());

        return redirect()->route('companies.index')
            ->with('success', 'Company successfully created');
    }

    // ======================
    // LIST DATA FOR DEVEXTREME AJAX
    // ======================
    public function list(Request $request)
    {
        $search = $request->search ?? '';
        $companies = Companies::where('comp_state','1')
            ->where('comp_name','like',"%$search%")
            ->get();

        return response()->json([
            'data' => $companies,
            'totalCount' => $companies->count()
        ]);
    }

    // ======================
    // EDIT PAGE (OPTIONAL)
    // ======================
    public function edit($id)
    {
        $company = Companies::findOrFail($id);
        return view('masters.company.update', compact('company'));
    }

    // ======================
    // UPDATE COMPANY VIA AJAX
    // ======================
    public function update(Request $request, $id)
    {
        try {
            $company = Companies::findOrFail($id);

            // Ambil semua input kecuali id dan _token
            $data = $request->except(['id', '_token']);

            // Jika ada file logo, handle upload
            if ($request->hasFile('comp_logo')) {
                $file = $request->file('comp_logo');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->storeAs('public/companies', $filename);

                // hapus logo lama jika ada
                if ($company->comp_logo && file_exists(storage_path('app/public/companies/'.$company->comp_logo))) {
                    @unlink(storage_path('app/public/companies/'.$company->comp_logo));
                }

                $data['comp_logo'] = $filename;
            }

            $company->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Company successfully updated',
                'code' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => 500
            ]);
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $res = Companies::where('id', $id)->update($request->except(['id']));

    //     if (!$res) {
    //         return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
    //     }

    //     return response()->json(['status' => 'success', 'message' => 'Data successfully edited', 'code' => 200]);
    // }

    // ======================
    // PARTNER COMPANY UPDATE
    // ======================
    public function partnerCompanyOpen()
    {
        $company = Companies::with('organization')
            ->where('id', $this->compid())
            ->where('comp_state', '1')
            ->first();

        return view('masters.company.partner_update', compact('company'));
    }

    public function partnerCompanyUpdate(Request $request, string $id)
    {
        $form = $request->get('form', []);

        Companies::where('id', $id)->update([
            'comp_name'    => $form['comp_name'] ?? '',
            'comp_address' => $form['comp_address'] ?? '',
            'comp_email'   => $form['comp_email'] ?? ''
        ]);

        return response()->json(['status' => 'success', 'message' => 'Data successfully updated', 'code' => 200]);
    }
}
