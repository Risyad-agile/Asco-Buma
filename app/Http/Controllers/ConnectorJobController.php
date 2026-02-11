<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Services\ConnectorService;

use App\Models\ConnectorJob;
use App\Models\ConnectorSource;
use App\Models\Companies;

class ConnectorJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->organization->company->id;
    }

    public function browse()
    {
        $jobs = DB::table('connector_jobs as jobs')
            ->leftJoin('connector_source as srce', 'srce.id', '=', 'jobs.connector_source_id')
            ->leftJoin('companies as comp', 'comp.id', '=', 'srce.comp_id')
            ->select(
                'srce.comp_id',
                'comp.comp_name',
                'jobs.id',
                'jobs.connector_source_id',
                'jobs.job_name',
                'jobs.schedule_time',
                'jobs.schedule_type',
                'jobs.days_of_week',
                'jobs.is_active',
                'jobs.last_run_at',
                'jobs.next_run_at'
            )
            ->orderBy('jobs.id', 'asc')
            ->get();

        $sources = ConnectorSource::with('company')
            ->where('is_active', 1)
            ->get();

        $companies = Companies::select('id as comp_id', 'comp_name')->get();

        return view('connector_job.browse', compact('jobs', 'sources', 'companies'));
    }


    // public function browse()
    // {
    //     $jobs = ConnectorJob::with('source.company')->orderBy('id', 'asc')->get();
    //     $sources = ConnectorSource::with('company')->where('is_active', 1)->get();
    //     $companies = Companies::select('id as comp_id', 'comp_name')->get(); 

    //     return view('connector_job.browse_job', compact('jobs', 'sources', 'companies'));
    //     // return view('connector_job.browse', compact('jobs', 'sources', 'companies'));
    // }


    public function create(Request $request)
    {
        // $companies = Companies::where('is_active', 1)->get();

        $query = ConnectorSource::with(['company', 'target'])
                    ->where('is_active', 1);

        if ($request->comp_id) {
            $query->where('comp_id', $request->comp_id);
        }

        $sources = $query->get();

        return view('connector_job.create', compact('sources'));
        // return view('connector_job.create', compact('sources', 'companies'));
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'connector_source_id' => 'required|exists:connector_source,id',
            'job_name' => 'required|string|max:255',
            'schedule_type' => 'required|string',
            'schedule_time' => 'nullable',
            'days_of_week' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        ConnectorJob::create($validated);

        return redirect()->route('connector_job.browse')->with('success', 'Job created successfully!');
    }

    public function edit($id)
    {
        $job = ConnectorJob::with('source')->findOrFail($id);

        $query = ConnectorSource::with(['company', 'target'])
                    ->where('is_active', 1);

        // ambil comp_id dari source milik job
        if ($job->source && $job->source->comp_id) {
            $query->where('comp_id', $job->source->comp_id);
        }

        $sources = $query->get();

        return view('connector_job.update', compact('job', 'sources'));
    }

    // public function edit($id)
    // {
    //     $job = ConnectorJob::findOrFail($id);
    //     $sources = ConnectorSource::where('is_active', 1)->get();
    //     return view('connector_job.update', compact('job', 'sources'));
    // }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'connector_source_id' => 'required|exists:connector_source,id',
            'job_name' => 'required|string|max:255',
            'schedule_type' => 'required|string',
            'schedule_time' => 'nullable',
            'days_of_week' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $job = \App\Models\ConnectorJob::findOrFail($id);
        $job->update($validated);

        return redirect()->route('connector_job.browse')->with('success', 'Job updated successfully!');
    }


    public function setStatus(Request $request)
    {
        $job = ConnectorJob::find($request->id);

        if (!$job) {
            return response()->json(['error' => 'Job not found.'], 404);
        }

        $job->is_active = !$job->is_active;
        $job->save();

        return response()->json(['success' => true, 'new_status' => $job->is_active]);
    }


    public function runFetchJob(Request $request)
    {
        $job = ConnectorJob::with('source.target')->find($request->id);
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }
        
        $source = $job->source;

        if ($request->comp_id) {
            $source->company_id = $request->comp_id;
        }

        try {
            $connectorService = new ConnectorService();
            $result = $connectorService->fetchAndStoreFromClient($source);

            // Logging hasil
            \Log::info("Running FETCH job: {$job->job_name}", [
                'source' => $source->conn_source_name,
                'target' => optional($source->target)->conn_target_name,
                'result' => $result,
            ]);

            return response()->json([
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'Unknown result.',
                'count'   => $result['count'] ?? 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    } 
    
    public function runPushJob(Request $request)
    {
        $job = ConnectorJob::with('source.target')->find($request->id);
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $source = $job->source;
        $target = $source->target;

        // 🧠 di sini nanti bisa panggil service untuk generate Excel dan upload ke S3 Envizi
        \Log::info("Running PUSH job: {$job->job_name}", [
            'source' => $source->conn_source_name,
            'target' => optional($target)->conn_target_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Push job '{$job->job_name}' executed successfully."
        ]);
    }


}
