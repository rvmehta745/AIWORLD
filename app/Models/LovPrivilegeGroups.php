<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LovPrivilegeGroups extends Model
{
    use HasFactory;
    protected $table = 'lov_privilege_groups';

    protected $fillable = [
        'name',
        'is_default',
        'is_active',
    ];
}
