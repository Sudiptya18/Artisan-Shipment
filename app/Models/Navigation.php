<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Navigation extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'route',
        'url',
        'icon',
        'permissions',
        'parent_id',
        'order_by',
        'is_enabled',
        'is_visible',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_enabled' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->ordered();
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true)->where('is_enabled', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_by')->orderBy('title');
    }
}
