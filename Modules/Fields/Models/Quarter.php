<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Models\User;

class Quarter extends Model
{
    use HasFactory;

    public function plants(): HasMany
    {
        return $this->hasMany(Plant::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function getCountPlantsAttribute(): int
    {
        return $this->plants->count();
    }

    public function harvests()
    {
        return $this->belongsToMany(Harvest::class, 'harvest_quarter');
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }
}
