<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LovPrivileges extends Model
{
    use HasFactory;
    protected $table = 'lov_privileges';

    protected $fillable = [
        'sequence',
        'group_id',
        'parent_id',
        'name',
        'path',
        'permission_key',
        'is_active',
    ];
    public function group()
    {
        return $this->hasOne(LovPrivilegeGroups::class, 'id', 'group_id');
    }

    public function parent()
    {
        return $this->hasOne(LovPrivileges::class, 'id', 'parent_id');
    }
    public function child()
    {
        return $this->hasMany(LovPrivileges::class, 'parent_id', 'id')->select(['id', 'group_id', 'parent_id', 'path', 'name', 'permission_key', 'is_active']);
    }
}
