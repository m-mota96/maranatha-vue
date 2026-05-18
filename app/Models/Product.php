<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'product_type_id',
        'name',
        'barcode',
        'brand',
        'price',
        'discounted_price',
        'type_sale',
        'content',
        'abreviation',
        'description',
        'stock',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }

    public function productType() {
        return $this->belongsTo(ProductType::class);
    }
}
