<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonFeature extends Model
{
    protected $fillable = ['addon_id', 'feature_id', 'value'];
    public $timestamps = false;

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}