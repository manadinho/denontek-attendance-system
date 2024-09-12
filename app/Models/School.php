<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class School extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function owners():BelongsToMany
    {
        return $this->belongsToMany(Owner::class);
    }

    public function schoolSetting():HasOne
    {
        return $this->hasOne(SchoolSetting::class);
    }
}
