<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;

class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'reference_id',
        'sale_id',
        'type',
        'quantity',
        'expiration_date',
        'batch',
        'discount',
        'price',
        'product_cost',
        'description',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function reference() {
        return $this->belongsTo(Reference::class);
    }

    public function sale() {
        return $this->belongsTo(Sale::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
