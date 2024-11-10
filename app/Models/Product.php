<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'created_at',
        'updated_at',
        'avatar'
    ];

    public function getAvatarAttribute($value)
    {
        return $value ? url($value) : null;
    }

    public function galleries()
    {
        return $this->hasMany(ProductGallery::class, 'product_id');
    }
}
