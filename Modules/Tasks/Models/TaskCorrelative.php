<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskCorrelative extends Model
{
    use HasFactory;

    protected $table = 'task_correlative';

    protected $fillable = ['correlative', 'year'];

    public function getCorrelative()
    {
        return str_pad($this->correlative, 3, '0', STR_PAD_LEFT).'_'.substr($this->year, -2);
    }
}
