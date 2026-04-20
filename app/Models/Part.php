<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $fillable = [
        'sku', 'name', 'category', 'purchase_price', 'selling_price', 
        'stock_quantity', 'min_stock', 'safety_stock', 'supplier_id', 'image', 'barcode'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
