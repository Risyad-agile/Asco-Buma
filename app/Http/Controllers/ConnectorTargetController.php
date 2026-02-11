<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConnectorTarget;

class ConnectorTargetController extends Controller
{
    public function browse()
    {
        $connector = \App\Models\ConnectorTarget::all()->map(function ($item) {
            $config = json_decode($item->config_json, true);
            $item->bucket = $config['bucket'] ?? ($config['path'] ?? null);
            $item->AccessKey = $config['access_key'] ?? ($config['username'] ?? null);
            $item->folder = $item->conn_target_folder ?? null;
            return $item;
        });

        return view('connector_target.browse', compact('connector'));
    }


    public function create()
    {
        return view('connector_target.create');
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $target = new ConnectorTarget();
        $target->conn_target_name = $data['conn_target_name'];
        $target->conn_target_type = $data['conn_target_type'];
        $target->conn_target_folder = $data['conn_target_folder'];
        $target->config_json = $data['config_json'] ?? '{}';
        $target->is_default = false;
        $target->is_active = true;
        $target->save();

        return response()->json(['success' => true]);
    }


    public function edit($id)
    {
        $target = ConnectorTarget::findOrFail($id);

        // decode JSON biar bisa diisi ke form
        $config = [];
        if (!empty($target->config_json)) {
            $config = json_decode($target->config_json, true);
        }

        return view('connector_target.update', compact('target', 'config'));
    }   

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'conn_target_name' => 'required|string|max:150',
            'conn_target_type' => 'required|in:S3,FTP,LOCAL,AZURE',
            'config_json' => 'nullable|string',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $target = ConnectorTarget::findOrFail($id);
        $target->update([
            'conn_target_name' => $validated['conn_target_name'],
            'conn_target_type' => $validated['conn_target_type'],
            'conn_target_folder' => $request->input('conn_target_folder'),
            'config_json' => $validated['config_json'] ?? null,
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json(['success' => true]);
    }

    public function setStatus(Request $request)
    {
        $id = $request->id;

        if (!$id) {
            return response()->json(['error' => 'Missing ID'], 400);
        }

        $current = \DB::table('connector_target')->where('id', $id)->value('is_active');
        $newStatus = $current ? 0 : 1;

        \DB::table('connector_target')->where('id', $id)->update(['is_active' => $newStatus]);

        return response()->json([
            'success' => true,
            'id' => $id,
            'new_status' => $newStatus
        ]);
    }
 
    public function getConfig($id)
    {
        $target = ConnectorTarget::find($id);

        if (!$target) {
            return response()->json(['error' => 'Target not found'], 404);
        }

        $config = is_array($target->config_json)
            ? $target->config_json
            : json_decode($target->config_json, true);

        return response()->json([
            'conn_target_type' => $target->conn_target_type,
            'config_json' => $config ?? [],
        ]);
    }
}
