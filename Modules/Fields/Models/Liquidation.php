<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Liquidation extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($liquidation) {
            $date = Carbon::parse($liquidation->date);
            $liquidation->year = $date->year;
            $liquidation->week = $date->weekOfYear;
        });
    }

    public function importer()
    {
        return $this->belongsTo(Importer::class);
    }

    public function liquidationProducts()
    {
        return $this->hasMany(LiquidationProduct::class);
    }

    public function categoryProducts()
    {
        return $this->hasManyThrough(CategoryProduct::class, LiquidationProduct::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
