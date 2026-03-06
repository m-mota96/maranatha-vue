<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentServiceStaff extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'appointment_service';

    protected $fillable = [
        'appointment_id',
        'service_id',
        'staff_id',
        'sale_id',
        'price',
        'discounted_price',
        'start_time',
        'end_time',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function service() {
        return $this->belongsTo(Service::class);
    }

    public function staff() {
        return $this->belongsTo(Staff::class);
    }
}
