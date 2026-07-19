<?php

namespace App\Models\Foundation;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoundationAdmin extends Model
{
    protected $table = 'foundation_admins';

    protected $fillable = ['foundation_id','user_id','role'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
