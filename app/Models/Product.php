<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantPrice;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];



    public function prices(){
        return  $this->hasMany('App\Models\ProductVariantPrice', 'product_id', 'id');
    }

    public function variant(){
        return  $this->hasMany('App\Models\ProductVariant', 'product_id', 'id');
    }

}
