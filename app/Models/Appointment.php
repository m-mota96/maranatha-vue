<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;
    
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
        return $this->belongsTo(AppointmentStatus::class, 'appointment_status_id');
    }

    public function services() {
        return $this->belongsToMany(Service::class)->withPivot(['price', 'start_time', 'end_time'])->orderBy('start_time');
    }

    public function servicesGrouped() {
        return $this->belongsToMany(Service::class)
        ->select(
            'services.id',
            'services.name',
            'services.time',
            DB::raw('COUNT(services.id) as quantity'),
            DB::raw('MIN(appointment_service.price) as price'),
            DB::raw('MIN(appointment_service.start_time) as start_time')
        )
        ->groupBy('services.id', 'services.name')
        ->orderBy('start_time');
    }

    public function staffs() {
        return $this->belongsToMany(Staff::class);
    }

    public function sale() {
        return $this->hasOne(Sale::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
