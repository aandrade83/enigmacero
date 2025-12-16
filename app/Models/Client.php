<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'bucket_folder',
        'internal_email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
