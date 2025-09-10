<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'service_type_id', 'name', 'price', 'discounted_price', 'time', 'color', 'status', 'created_by', 'updated_by', 'deleted_by', 
    ];

    public function service_type() {
        return $this->belongsTo(ServiceType::class);
    }

    public function staff() {
        return $this->belongsToMany(Staff::class)->where('status', 1)->orderBy('name');
    }

    public function appoiments() {
        return $this->belongToMany(Appoiment::class);
    }
}
