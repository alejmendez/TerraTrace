<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\Fields\Database\Factories\FieldFactory;

class Field extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return FieldFactory::new();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);
    }

    public function plants(): HasManyThrough
    {
        return $this->hasManyThrough(Plant::class, Quarter::class);
    }

    public function harvests(): HasManyThrough
    {
        return $this->hasManyThrough(Harvest::class, Quarter::class);
    }

    public function getCountPlantsAttribute(): int
    {
        return $this->plants->count();
    }

    public function getCountQuartersAttribute(): int
    {
        return $this->quarters->count();
    }

    public function documents()
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
