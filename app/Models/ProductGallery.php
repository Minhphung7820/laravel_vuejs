<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductGallery extends Model
{
    protected $table = 'product_galleries';

    protected $guarded = ['id'];
    protected $fillable = [
        'product_id',
        'url',
        'created_at',
        'updated_at',
    ];

    public function getUrlAttribute($value)
    {
        return $value ? url($value) : null;
    }
}
