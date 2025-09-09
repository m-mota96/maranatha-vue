<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppoimentStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 
    ];

    public function appoiments() {
        return $this->hasMany(Appoiment::class);
    }
}
