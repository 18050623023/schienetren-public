<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;


    protected $fillable = [
        'name',
        'type',
        'description',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeForProduct($query)
    {
        return $query->whereIn('type', ['product', 'both']);
    }

    public function scopeForService($query)
    {
        return $query->whereIn('type', ['service', 'both']);
    }
}
