<?php

namespace App\Models\Gate;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdGateEvent extends Model
{
    protected $table = 'id_gate_events';
    public $timestamps = false;

    protected $fillable = [
        'school_id','id_gate_device_id','user_id','direction','scanned_at',
    ];

    protected $casts = ['scanned_at' => 'datetime'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IdGateDevice::class, 'id_gate_device_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
