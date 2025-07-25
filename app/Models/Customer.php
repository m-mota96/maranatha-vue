<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'birthdate',
        'email',
        'phone',
        'company_name',
        'rfc',
        'address',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
