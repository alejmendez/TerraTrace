<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantDetail extends Model
{
    use HasFactory;

    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
