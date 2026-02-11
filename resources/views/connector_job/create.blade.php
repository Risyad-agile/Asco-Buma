@extends('layouts.master')
@section('content')
<div class="card p-4">
    <h3 class="mb-3">Create New Job Schedule</h3>
    <form method="POST" action="{{ route('connector_job.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Connector Source</label>
            <select name="connector_source_id" class="form-select" required>
                <option value="">-- Select Connector --</option>
                @foreach ($sources as $src)
                    <option value="{{ $src->id }}">{{ $src->conn_source_name }}</option>
                @endforeach
            </select>
        </div> 

        <div class="mb-3">
            <label class="form-label">Job Name</label>
            <input type="text" name="job_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Schedule Type</label>
            <select name="schedule_type" class="form-select" required>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="manual">Manual</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Schedule Time (HH:MM)</label>
            <input type="time" name="schedule_time" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Days of Week (if weekly)</label>
            <input type="text" name="days_of_week" class="form-control" placeholder="e.g. Monday, Wednesday, Friday">
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Job</button>
        <a href="{{ route('connector_job.browse') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
