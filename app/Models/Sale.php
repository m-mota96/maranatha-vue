<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Sale extends Model
{
    protected $fillable = [
        'status_sale_id',
        'payment_method_id',
        'appointment_id',
        'cash',
        'card',
        'subtotal',
        'discount',
        'type_discount',
        'total',
        'observations',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function statusSale() {
        return $this->belongsTo(StatusSale::class);
    }

    public function paymentMethod() {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function appointment() {
        return $this->belongsTo(Appointment::class);
    }

    public function services() {
        return $this->hasMany(AppointmentServiceStaff::class);
    }

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }
    
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
