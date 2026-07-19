<?php

namespace App\Http\Resources\PPDB;

use App\Models\PPDB\PpdbApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PpdbApplication */
class PpdbApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'registration_no' => $this->registration_no,
            'jalur'           => $this->jalur,
            'student_name'    => $this->student_name,
            'nisn'            => $this->nisn,
            'date_of_birth'   => $this->date_of_birth?->toDateString(),
            'gender'          => $this->gender,
            'address'         => $this->address,
            'district'        => $this->district,
            'city'            => $this->city,
            'distance_km'     => $this->distance_km,
            'previous_school' => $this->previous_school,
            'parent_name'     => $this->parent_name,
            'parent_phone'    => $this->parent_phone,
            'parent_email'    => $this->parent_email,
            'average_score'   => $this->average_score,
            'ranking_score'   => $this->ranking_score,
            'rank_position'   => $this->rank_position,
            'status'          => $this->status,
            'reviewer_note'   => $this->reviewer_note,
            'submitted_at'    => $this->submitted_at?->toIso8601String(),
            'verified_at'     => $this->verified_at?->toIso8601String(),
            'accepted_at'     => $this->accepted_at?->toIso8601String(),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
