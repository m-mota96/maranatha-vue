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
        'name',
        'barcode',
        'brand',
        'price',
        'discounted_price',
        'type_sale',
        'content',
        'abreviation',
        'description',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function inventories() {
        return $this->hasMany(Inventory::class);
    }
}
