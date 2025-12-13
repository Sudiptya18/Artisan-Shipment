<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'pcs_cases',
        'cases_pal',
        'cases_lay',
        'container_load',
        'cases_20ft_container',
        'cases_40ft_container',
        'total_shelf_life',
        'gross_weight_cs_kg',
        'net_weight_cs_kg',
        'cbm',
        'hs_code_id',
        'rate',
        'shipment_title_id',
        'commodity_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'gross_weight_cs_kg' => 'decimal:2',
        'net_weight_cs_kg' => 'decimal:2',
        'cbm' => 'decimal:2',
        'rate' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function hsCode(): BelongsTo
    {
        return $this->belongsTo(Hscode::class, 'hs_code_id');
    }

    public function shipmentTitle(): BelongsTo
    {
        return $this->belongsTo(Title::class, 'shipment_title_id');
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}
