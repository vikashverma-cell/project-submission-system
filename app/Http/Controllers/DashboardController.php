<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Constants\Constants;

class DashboardController extends Controller
{
     public function index()
    {
        $user=auth()->user();
        $query=Project::query();

        if($user->role!='admin')
        {
            $query->where('user_id', $user->id);
        } 

        if(request()->filled('status'))
        {
            $query->where('status', request('status'));
        }

        if(request()->filled('submitter'))
        {
            $query->where('user_id', request('submitter'));
        }

        if(request()->filled('submission_date'))
        {
            $query->whereDate('created_at', request('submission_date'));
        }
        $countQuery = clone $query;
        $projects = $query->latest()->paginate(10);
        $submitters=\App\Models\User::where('role', 'user')->get();
        $total = $countQuery->count();
        $pending = (clone $countQuery)->where('status', Constants::PROJECT_STATUS['pending'])->count();
        $approved = (clone $countQuery)->where('status', Constants::PROJECT_STATUS['approved'])->count();
        $rejected = (clone $countQuery)->where('status', Constants::PROJECT_STATUS['rejected'])->count();

        $pendingPercent = $total ? round(($pending / $total) * 100, 2) : 0;
        $approvedPercent = $total ? round(($approved / $total) * 100, 2) : 0;
        $rejectedPercent = $total ? round(($rejected / $total) * 100, 2) : 0;
        return view('dashboard', compact('projects','submitters', 'total', 'pending', 'approved', 'rejected', 'pendingPercent', 'approvedPercent', 'rejectedPercent'));
    }
}
