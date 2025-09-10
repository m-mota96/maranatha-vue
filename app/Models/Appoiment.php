<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appoiment extends Model
{
    protected $fillable = [
        'customer_id',
        'appoiment_status_id',
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
        return $this->belongsTo(AppoimentStatus::class);
    }

    public function services() {
        return $this->belongsToMany(Service::class);
    }

    public function staffs() {
        return $this->belongsToMany(Staff::class);
    }
}
