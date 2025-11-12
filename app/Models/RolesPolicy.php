<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolesPolicy extends Model
{
    use HasFactory;

    protected $table = 'roles_policies';

    protected $fillable = [
        'user_id',
        'role_id',
        'navigation_id',
    ];

    /**
     * Get the user that owns the policy.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role that owns the policy.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the navigation that owns the policy.
     */
    public function navigation(): BelongsTo
    {
        return $this->belongsTo(Navigation::class);
    }
}

