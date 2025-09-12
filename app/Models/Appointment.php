<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'customer_id',
        'appointment_status_id',
        'date',
        'horary',
        'cost',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function status() {
        return $this->belongsTo(AppointmentStatus::class);
    }

    public function services() {
        return $this->belongsToMany(Service::class);
    }

    public function staffs() {
        return $this->belongsToMany(Staff::class);
    }
}
