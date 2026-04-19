<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    // public function toArray(Request $request): array
    // {
    //     return parent::toArray($request);
    // }

    public function toArray(Request $request)
    {
        return [

            'id'=>$this->id,

            'title'=>$this->title,

            'description'=>$this->description,

            'status'=>$this->status,

            'submitted_by'=>$this->user->name,

            'created_at'=>$this->created_at

        ];
    }
}
