<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'status_sale_id',
        'payment_method_id',
        'appointment_id',
        'cash',
        'card',
        'total',
        'observations',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function status() {
        return $this->belongsTo(StatusSale::class);
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }
}
