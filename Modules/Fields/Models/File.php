<?php

namespace Modules\Fields\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['path', 'type', 'name'];

    public function fileable()
    {
        return $this->morphTo();
    }
}
