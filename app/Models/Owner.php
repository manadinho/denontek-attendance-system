<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Owner extends Authenticatable
{
    use HasFactory;

    protected $guarded = ['id', 'email'];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class);
    }
}
