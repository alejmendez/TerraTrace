<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Users\Models\User;

class Harvest extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saving(function ($harvest) {
            $date = Carbon::parse($harvest->date);
            $harvest->year = $date->year;
            $harvest->week = $date->weekOfYear;
        });
    }

    public function details(): HasMany
    {
        return $this->hasMany(HarvestDetail::class);
    }

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    public function quarters()
    {
        return $this->belongsToMany(Quarter::class, 'harvest_quarter');
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'batch_harvest');
    }
}
