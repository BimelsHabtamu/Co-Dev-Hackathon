<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category',
        'sku',
        'cost_price',
        'selling_price',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'cost_price'      => 'decimal:2',
            'selling_price'   => 'decimal:2',
            'stock_quantity'  => 'integer',
        ];
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
