@extends('layouts.master')
@section('content')
<div class="card p-4">
    <h3 class="mb-3">Edit Job Schedule</h3>
    <form method="POST" action="{{ route('connector_job.update', $job->id) }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Connector Source</label>
            <select name="connector_source_id" class="form-select" required>
                @foreach ($sources as $src)
                    <option value="{{ $src->id }}" {{ $src->id == $job->connector_source_id ? 'selected' : '' }}>
                        {{ $src->conn_source_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Job Name</label>
            <input type="text" name="job_name" value="{{ $job->job_name }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Schedule Type</label>
            <select name="schedule_type" class="form-select" required>
                <option value="daily" {{ $job->schedule_type == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ $job->schedule_type == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="manual" {{ $job->schedule_type == 'manual' ? 'selected' : '' }}>Manual</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Schedule Time</label>
            <input type="time" name="schedule_time" value="{{ $job->schedule_time }}" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Days of Week</label>
            <input type="text" name="days_of_week" value="{{ $job->days_of_week }}" class="form-control" placeholder="e.g. Monday, Wednesday">
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-control" rows="3">{{ $job->remarks }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Job</button>
        <a href="{{ route('connector_job.browse') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
