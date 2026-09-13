<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DogVaccine extends Model
{
    use HasFactory;

    public function dog(): BelongsTo
    {
        return $this->belongsTo(Dog::class);
    }
}
