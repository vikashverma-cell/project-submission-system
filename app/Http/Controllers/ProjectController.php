<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Approval;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Constants\Constants;
use Mail;
use App\Mail\ProjectStatusMail;

class ProjectController extends Controller
{
    public function create()
    {
        return view('projects.create');
    }
    public function store (Request $request) {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'nullable|file'
        ]);

        // file upload
        $filePath =  null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('projects', 'public');
        }
        
        $project = Project::create([
            'user_id' => auth()->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'file' => $filePath  
        ]);

        Mail::to($project->user->email)
            ->queue(new ProjectStatusMail($project, 'Submitted'));

        return redirect()->route('dashboard')->with('success', 'Project submitted.');
    }

    public function approve (Request $request, $id, Project $project) {
        $project = Project::find($id);
        if (!$project) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        // use policy
        $this->authorize('approve', $project); 

        if ($project->status == Constants::PROJECT_STATUS['approved']) {
            return back()->with('error', 'Project already approved.');
        }

        if ($project->status == Constants::PROJECT_STATUS['rejected']) {
            return back()->with('error', 'Rejected project cannot be approved.');
        }

        //call stored procedure
        DB::select("CALL sp_approve_project(?,?)",[$id,auth()->id()]);

        Mail::to($project->user->email)
            ->queue(new ProjectStatusMail($project, 'Approved'));

        return back()->with('success', 'Project Approved.');
    }

    public function reject (Request $request, $id) {
        $project = Project::find($id);
        if (!$project) {
            return redirect()->back()->with('error', 'Record not found.');
        }
        $this->authorize('approve', $project);
        if ($project->status == Constants::PROJECT_STATUS['approved']) {
            return back()->with('error', 'Approved project cannot be rejected.');
        }

        if ($project->status == Constants::PROJECT_STATUS['rejected']) {
            return back()->with('error', 'Project already rejected.');
        }
        $project->update([
            'status' => Constants::PROJECT_STATUS['rejected']
        ]);
        Approval::create([
            'project_id' => $project->id,
            'admin_id' => auth()->id(),
            'status' => Constants::PROJECT_STATUS['rejected'],
            'reason' => $request->reason
        ]);

        AuditLog::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'action' => 'rejected'
        ]);

        Mail::to($project->user->email)
            ->queue(new ProjectStatusMail($project, 'Rejected', $request->reason));
        return back()->with('success', 'Project rejected.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:projects,id'
        ]);
        DB::beginTransaction();
        try{
            foreach($request->ids as $projectId){
                DB::select("CALL sp_approve_project(?,?)",[$projectId ,auth()->id()]);
            }
            DB::commit();
            return redirect()->back()->with('success','Selected projects approved successfully.');
           
        } catch(\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Bulk approval failed.');
        }
        
        return back()->with('success','Bulk approval completed');
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:projects,id',
            'reason'=>'required'
        ]);
        DB::beginTransaction();
        try{
            foreach($request->ids as $projectId)
            {
                Project::where('id', $projectId)->update(['status' => Constants::PROJECT_STATUS['rejected']]);

                Approval::create([
                    'project_id'=>$projectId,
                    'admin_id'=>auth()->id(),
                    'status' => Constants::PROJECT_STATUS['rejected'],
                    'reason'=>$request->reason
                ]);

                AuditLog::create([
                    'project_id'=>$projectId,
                    'user_id'=>auth()->id(),
                    'action'=>'bulk rejected'
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success','Selected projects rejected.');
           
        } catch(\Excetion $e) {
            DB::rollback();
            return redirect()->back()->withErrors('Bulk rejection failed.');
        }
        
        return back()->with('success','Bulk rejected completed');
    }
    
}
