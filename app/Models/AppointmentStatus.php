<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name', 
    ];

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}
