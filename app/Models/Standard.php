<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'standard_user', 'standard_id', 'user_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
