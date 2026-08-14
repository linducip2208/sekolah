<?php

namespace App\Models\Workflow;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRequest extends SchoolModel
{
    protected $table = 'workflow_requests';

    protected $fillable = [
        'school_id', 'requester_id', 'type', 'title', 'description', 'payload',
        'status', 'approver_id', 'submitted_at', 'decided_at', 'decision_note',
    ];

    protected $casts = [
        'payload'      => 'array',
        'submitted_at' => 'datetime',
        'decided_at'   => 'datetime',
    ];

    public const TYPES = [
        'leave'           => 'Cuti / Izin',
        'purchase'        => 'Pengadaan',
        'expense'         => 'Pengeluaran',
        'student_transfer' => 'Pindah Siswa',
        'discount'        => 'Diskon / Keringanan',
        'refund'          => 'Pengembalian Dana',
        'other'           => 'Lainnya',
    ];

    public const STATUSES = [
        'draft'        => 'Draft',
        'submitted'    => 'Diajukan',
        'under_review' => 'Dalam Review',
        'approved'     => 'Disetujui',
        'rejected'     => 'Ditolak',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
