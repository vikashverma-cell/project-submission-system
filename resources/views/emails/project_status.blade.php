<h2>Project Notification</h2>

<p>
Project Name:

{{ $project->title }}
</p>

<p>
Status:

{{ $status }}
</p>

<p>
Time:

{{ now() }}
</p>

@if($reason)

<p>
Reason:

{{ $reason }}
</p>

@endif