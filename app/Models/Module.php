<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    public function schools()
    {
        return $this->belongsToMany(School::class, 'school_module', 'module_id', 'school_id');
    }
}
