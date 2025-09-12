<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentServiceStaff extends Model
{
    protected $table = 'appointment_service';

    public $timestamps = false;

    protected $fillable = [
        'appointment_id',
        'service_id',
        'staff_id',
        'price',
        'discounted_price',
        'start_time',
        'end_time',
    ];

    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function staff() {
        return $this->belongsTo(Staff::class);
    }
}
