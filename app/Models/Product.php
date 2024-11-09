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
    ];
}
