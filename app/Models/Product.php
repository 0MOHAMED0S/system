<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'cost_price',
        'sale_price',
        'quantity',
        'min_quantity',
        'sku'
    ];


    protected static function booted()
    {
        static::creating(function ($product) {
            $product->sku = self::generateSku();
        });
    }

    private static function generateSku(): string
    {
        do {
            $sku = 'SKU' . random_int(100000000, 999999999);
        } while (self::where('sku', $sku)->exists());

        return $sku;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
