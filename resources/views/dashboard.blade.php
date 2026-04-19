@extends('layouts.app')

@section('content')
    <div class="container">

        <h3 class="mb-4">Dashboard</h3>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-dark text-white p-3">
                    <h5>Total</h5>
                    <h3>{{ $total }}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white p-3">
                    <h5>Pending</h5>
                    <h3>{{ $pending }} ({{ $pendingPercent }}%)</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white p-3">
                    <h5>Approved</h5>
                    <h3>{{ $approved }} ({{ $approvedPercent }}%)</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white p-3">
                    <h5>Rejected</h5>
                    <h3>{{ $rejected }} ({{ $rejectedPercent }}%)</h3>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Project List
            </div>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">

                    {{-- Left side (empty or title use kar sakte ho) --}}
                    <div>
                        <h5 class="mb-0">Projects</h5>
                    </div>

                    {{-- Right side (Create button) --}}
                    @if (auth()->user()->role === 'user')
                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                            Create Project
                        </a>
                    @endif

                </div>
                {{-- FILTER SECTION START --}}
                <form method="GET" action="{{ route('dashboard') }}" class="row mb-4">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value=""> All Status </option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}> Pending </option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}> Approved </option>
                            <option value="2" {{ request('status') === '2' ? 'selected' : '' }}> Rejected </option>
                        </select>
                    </div>
                    @if (auth()->user()->role === 'admin')
                        <div class="col-md-3">
                            <select name="submitter" class="form-control">
                                <option value=""> All Submitters</option>
                                @foreach ($submitters as $submitter)
                                    <option value="{{ $submitter->id }}"
                                        {{ request('submitter') == $submitter->id ? 'selected' : '' }}>
                                        {{ $submitter->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <input type="date" name="submission_date" value="{{ request('submission_date') }}"
                            class="form-control">
                    </div>


                    <div class="col-md-1">
                        <button class="btn btn-info"> Filter </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary"> Reset </a>
                    </div>
                </form>

                {{-- FILTER SECTION END --}}
                @if (auth()->user()->role === 'admin')
                    <form method="POST" action="{{ route('projects.bulk.approve') }}" id="bulkApproveForm">
                        @csrf
                        <div id="selectedProjects"></div>
                        <button class="btn btn-primary mb-3">Bulk Approve</button>
                    </form>
                    <form method="POST" action="{{ route('projects.bulk.reject') }}" id="bulkRejectForm">
                        @csrf
                        <div id="rejectSelectedProjects"></div>
                        <input type="hidden" name="reason" id="bulkRejectReason">
                    </form>
                    <button type="button" class="btn btn-danger mb-3" onclick="showRejectReason()"> Bulk Reject</button>
                @endif
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @if (auth()->user()->role === 'admin')
                                <th width="50">
                                    <input type="checkbox" id="selectAll">
                                </th>
                            @endif
                            <th>Title</th>
                            <th>User</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Last Update</th>

                            @if (auth()->user()->role === 'admin')
                                <th>Action</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                @if (auth()->user()->role === 'admin')
                                    <td>
                                        {{-- Pending projects only --}}
                                        @if ($project->status == 0)
                                            <input type="checkbox" name="project_ids[]" value="{{ $project->id }}"
                                                class="projectCheckbox">
                                        @endif
                                    </td>
                                @endif
                                <td>{{ $project->title }}</td>
                                <td>{{ $project->user->name ?? '-' }}</td>
                                <td>{{ $project->created_at->format('d M Y') }}</td>
                                <td>
                                    @if ($project->status == 0)
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($project->status == 1)
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $project->updated_at->diffForHumans() }}</td>

                                <!-- Admin Actions -->
                                @if (auth()->user()->role === 'admin')
                                    <td>
                                        <a href="{{ route('projects.approve', $project->id) }}"
                                            class="btn btn-success btn-sm">Approve</a>

                                        <button class="btn btn-danger btn-sm" onclick="rejectProject({{ $project->id }})">
                                            Reject
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>

                </table>
                {{ $projects->appends(request()->query())->links() }}

            </div>
        </div>

    </div>

    <!-- Reject Modal -->
    <script>
        function rejectProject(id) {
            let reason = prompt("Enter rejection reason:");

            if (reason) {
                window.location.href = `/projects/reject/${id}?reason=` + reason;
            }
        }


        // bull approval submit
        document.getElementById('selectAll').addEventListener('click', function() {
            document.querySelectorAll('.projectCheckbox').forEach(function(box) {
                box.checked = document.getElementById('selectAll').checked;
            });
        });
        document.getElementById('bulkApproveForm').addEventListener('submit', function(e) {
            let checkedBoxes = document.querySelectorAll('.projectCheckbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one project.');
                return;
            }
            let hiddenInputs = '';
            checkedBoxes.forEach(function(box) {
                hiddenInputs += '<input type="hidden" name="ids[]" value="' + box.value + '">';
            });
            document.getElementById('selectedProjects').innerHTML = hiddenInputs;
        });

        // bulk rejection submit
        function showRejectReason() {
            let checkedBoxes = document.querySelectorAll('.projectCheckbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select projects first.');
                return;
            }
            let reason = prompt('Enter rejection reason');
            if (!reason) {
                return;
            }
            //inject reason
            let html = '';
            checkedBoxes.forEach(function(box) {
                html += '<input type="hidden" name="ids[]" value="' + box.value + '">';
            });
            document.getElementById('rejectSelectedProjects').innerHTML = html;
            // set reason
            document.getElementById('bulkRejectReason').value = reason;
            // Submit reject form
            document.getElementById('bulkRejectForm').submit();
        }
    </script>

@endsection
