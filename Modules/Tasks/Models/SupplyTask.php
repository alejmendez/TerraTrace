<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyTask extends Model
{
    use HasFactory;

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
