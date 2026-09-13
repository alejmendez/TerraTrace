<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    use HasFactory;

    public function liquidationProducts()
    {
        return $this->hasMany(LiquidationProduct::class);
    }
}
