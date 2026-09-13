<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    public function importer()
    {
        return $this->belongsTo(Importer::class);
    }

    public function harvests()
    {
        return $this->belongsToMany(Harvest::class, 'batch_harvest');
    }
}
