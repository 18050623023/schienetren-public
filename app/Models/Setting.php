<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use \Stancl\Tenancy\Database\Concerns\CentralConnection;

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];
}
