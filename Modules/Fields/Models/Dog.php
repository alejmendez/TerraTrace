<?php

namespace Modules\Fields\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Models\User;

class Dog extends Model
{
    use HasFactory;

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function vaccines(): HasMany
    {
        return $this->hasMany(DogVaccine::class);
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->attributes['birthdate'])->age;
    }
}
