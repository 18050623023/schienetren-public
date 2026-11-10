<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Domain as ModelsDomain;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends ModelsDomain
{
    protected $fillable = [
        'domain',
        'tenant_id',
    ];


    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
