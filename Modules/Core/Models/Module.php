<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_email',
        'author_url',
        'license',
        'license_url',
        'is_active',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public function getMenus()
    {
        return $this->menus()->orderBy('order')->get();
    }
}
