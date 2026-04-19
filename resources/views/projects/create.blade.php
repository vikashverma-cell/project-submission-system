@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4>Create Project</h4>
                </div>

                <div class="card-body">
                @if(session('success'))
                    <div id="successAlert" style="padding:10px; background:#d4edda; position:relative;">
                        {{ session('success') }}
                        <span onclick="this.parentElement.style.display='none'" 
                            style="position:absolute; right:10px; cursor:pointer;">✖</span>
                    </div>
                @endif
                    <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                            @error('title')
                                <div class="text-danger">{{ $message }}</div> 
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" name="file" class="form-control">
                        </div>

                        <button class="btn btn-primary w-10">Submit</button>
                    </form>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection