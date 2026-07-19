<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone,
            'avatar_url'  => $this->avatar_url,
            'role'        => $this->role_name,
            'school_id'   => $this->school_id,
            'locale'      => $this->locale,
            'is_active'   => $this->is_active,
            'school'      => $this->when(
                $this->relationLoaded('school') && $this->school,
                fn() => new SchoolResource($this->school)
            ),
            'permissions' => $this->when(
                $this->relationLoaded('roles'),
                fn() => $this->getAllPermissions()->pluck('name')
            ),
        ];
    }
}
