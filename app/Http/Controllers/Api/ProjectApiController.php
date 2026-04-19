<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\DB;

class ProjectApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'description'=>'required'
        ]);

        $project=Project::create([
            'user_id'=>auth()->user()->id,
            'title'=>$request->title,
            'description'=>$request->description,
            'status'=> Constants::PROJECT_STATUS['pending']
        ]);

        return response()->json(['message'=>'Project Created','data'=>new ProjectResource($project)],201);
    }

    public function approve($id)
    {
        $project=Project::findOrFail($id);
        // check policy
        $this->authorize('approve',$project);
        //stored procedure
        DB::select("CALL sp_approve_project(?,?)",[$id,auth()->id()]);
        return response()->json(['message'=>'Approved']);
    }
}
