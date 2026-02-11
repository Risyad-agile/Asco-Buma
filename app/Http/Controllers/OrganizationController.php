<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\Companies;
use GuzzleHttp\Client;
use Config;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function combo()
    {
        return response()->json(
            Companies::where('comp_state',1)
                ->select('id','comp_name')
                ->orderBy('comp_name')
                ->get()
        );
    }

    // ==========================
    // BROWSE
    // ==========================
    public function index()
    {
        $companies = Companies::where('comp_state',1)->get();
        return view('organization.browse', compact('companies'));
    }

    public function list(Request $request)
    {
        $query = Organization::with('company');

        if ($request->comp_id) {
            $query->where('comp_id', $request->comp_id);
        }

        if ($request->search) {
            $query->where(function($q) use ($request){
                $q->where('org_name','like','%'.$request->search.'%')
                ->orWhere('org_code','like','%'.$request->search.'%')
                ->orWhere('org_link','like','%'.$request->search.'%');
            });
        }

        return response()->json($query->get());
    }

    // ==========================
    // CREATE MANUAL
    // ==========================

    public function create()
    {
        $companies = Companies::where('comp_state',1)
            ->orderBy('comp_name')
            ->get();

        return view('organization.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'org_name' => 'required',
            'comp_id'  => 'required|exists:companies,id',
        ]);

        Organization::create([
            'org_name'  => $request->org_name,
            'org_link'  => $request->org_link,
            'org_state' => $request->org_state ?? 1,
            'comp_id'   => $request->comp_id,
        ]);

        return redirect()->route('organizations.index')
            ->with('success','Organization berhasil dibuat');
    }

    // ==========================
    // EDIT / UPDATE
    // ==========================

    public function edit($id)
    {
        $organization = Organization::findOrFail($id);
        $companies = Companies::where('comp_state',1)->orderBy('comp_name')->get();

        return view('organization.update', compact('organization','companies'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'org_name' => 'required',
            'comp_id'  => 'required|exists:companies,id',
        ]);

        $org = Organization::findOrFail($id);

        $org->update([
            'org_name'  => $request->org_name,
            'org_link'  => $request->org_link,
            'org_state' => $request->org_state ?? 1,
            'comp_id'   => $request->comp_id,
        ]);

        return redirect()->route('organizations.index')
            ->with('success','Organization berhasil diupdate');
    }
 
    // ==========================
    // REGISTER TO ENVIZI
    // ==========================
    public function registerEnvizi(Request $request)
    {
        $org = Organization::findOrFail($request->id);

        if($org->org_link){
            return response()->json(['status'=>'error','message'=>'Already registered']);
        }

        $enviziId = $this->registerToEnvizi($org);

        if ($enviziId) {
            $org->org_link = $enviziId;
            $org->save();

            return response()->json(['status'=>'success','message'=>'Registered to Envizi']);
        }

        return response()->json(['status'=>'error','message'=>'Register failed']);
    }

    private function registerToEnvizi($org)
    {
        try {
            $client = new Client([
                'base_uri' => config('asri.envizi.api_address.au_apac.url'),
                'headers' => [
                    'Authorization' => 'Bearer '.$this->getToken($org->comp_id),
                    'Accept' => 'application/json',
                ]
            ]);

            $response = $client->post('/organizations', [
                'json' => [
                    'name' => $org->org_name
                ]
            ]);

            $result = json_decode($response->getBody(), true);

            return $result['organizationId'] ?? null;

        } catch (\Exception $e) {
            \Log::error("Envizi Register Error: ".$e->getMessage());
            return null;
        }
    }

    private function getToken($compid)
    {
        // ambil token dari connector seperti sebelumnya
        return 'TOKEN_ENVIZI_DISINI';
    }
}
