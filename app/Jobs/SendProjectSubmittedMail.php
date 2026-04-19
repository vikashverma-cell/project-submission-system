<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendProjectSubmittedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    pulic $project;
    public function __construct($project)
    {
        return $this->project = $project;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($project->user->email)
                ->send(new ProjectMail($project));
    }
}
