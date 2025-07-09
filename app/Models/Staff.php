<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'position_id', 'name', 'first_name', 'last_name', 'birthdate', 'curp', 'rfc', 'email', 'phone', 'commission', 'status', 'created_by', 'updated_by', 'deleted_by',
    ];

    public function position() {
        return $this->belongsTo(Position::class);
    }

    public function schedules() {
        return $this->hasMany(StaffSchedule::class);
    }

    public function services() {
        return $this->belongsToMany(Service::class)->where('status', 1)->orderBy('name');
    }
}
