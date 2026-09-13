<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidationProduct extends Model
{
    use HasFactory;

    public function liquidation()
    {
        return $this->belongsTo(Liquidation::class);
    }

    public function categoryProduct()
    {
        return $this->belongsTo(CategoryProduct::class);
    }
}
