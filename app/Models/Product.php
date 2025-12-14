<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_title',
        'global_code',
        'description',
        'benefits',
        'pack_size',
        'brand_id',
        'category_id',
        'format_id',
        'origin_id',
        'status',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const STATUS_OPTIONS = [
        'ACTIVE',
        'DISCONTINUED-UI',
        'DISCONTINUED-ARTISAN',
        'REPLACEMENT',
        'REPLACEMENT & DISCONTINUED',
        'NEW CODE',
        'FUTURE DISCONTINUED',
        'NEW TENTATIVE',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(Format::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function productDetail(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductDetail::class);
    }
}
