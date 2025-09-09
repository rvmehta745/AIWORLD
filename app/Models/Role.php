<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait, Sluggable;

    public $primaryKey = 'id';

    public $table = "mst_roles";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'privileges',
        'is_editable',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_editable' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ]
        ];
    }
    public function createdBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function updatedBy()
    {
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }

    public function deletedBy()
    {
        return $this->hasOne(User::class, 'id', 'deleted_by');
    }

    /**
     * Get privileges as array
     */
    public function getPrivilegesArrayAttribute()
    {
        if (empty($this->privileges) || is_null($this->privileges)) {
            return [];
        }
        
        return array_filter(explode('#', $this->privileges), function($value) {
            return !empty($value) && is_numeric($value);
        });
    }

    /**
     * Set privileges from array
     */
    public function setPrivilegesArrayAttribute($value)
    {
        if (is_array($value)) {
            $this->privileges = '#' . implode('#', $value) . '#';
        }
    }

    /**
     * Check if role has specific privilege
     */
    public function hasPrivilege($privilegeId)
    {
        $privileges = $this->privileges_array;
        return in_array($privilegeId, $privileges);
    }

    /**
     * Get role with privileges details
     */
    public function withPrivilegesDetails()
    {
        $privileges = $this->privileges_array;
        
        if (empty($privileges) || !is_array($privileges)) {
            $this->privileges_details = [];
            return $this;
        }

        $privilegesDetails = \App\Models\LovPrivileges::select('id', 'name', 'permission_key', 'path', 'group_id', 'parent_id')
            ->whereIn('id', $privileges)
            ->where('is_active', 1)
            ->orderBy('sequence')
            ->get();

        $this->privileges_details = $privilegesDetails->toArray();
        return $this;
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope for editable roles
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', 1);
    }
}
