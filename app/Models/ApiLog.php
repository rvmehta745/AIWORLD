<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiLog extends Model
{
    use SoftDeletes;

    protected $table = 'api_logs';

    protected $fillable = [
        'user_id',
        'http_method',
        'api_endpoint',
        'ip_address',
        'user_agent',
        'request_body',
        'response_status_code',
        'response_body',
        'duration_ms',
        'is_error',
        'error_message',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'request_body' => 'array',
        'response_body' => 'array',
        'is_error' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}