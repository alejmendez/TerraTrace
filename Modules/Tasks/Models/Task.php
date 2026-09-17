<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Registry\EntityRegistry;

class Task extends Model
{
    use HasFactory;

    protected $casts = [
        'rows' => 'array',
    ];

    /**
     * Cross-module relationships.
     *
     * The Task model refers to entities owned by Fields (Field, Plant,
     * Quarter, Tool, Machinery, SecurityEquipment) and Users (User as
     * responsible). Rather than `use Modules\Fields\Models\Field;`
     * (which would force Tasks to recompile when Fields changes its
     * model class), we look the FQCN up at runtime via the registry.
     *
     * The registry is populated by each module's ServiceProvider::register(),
     * which runs before any model hydration, so by the time a Task
     * is loaded the lookups resolve.
     */
    public function field()
    {
        return $this->belongsTo(EntityRegistry::model('field'));
    }

    public function responsible()
    {
        return $this->belongsTo(EntityRegistry::model('user'), 'responsible_id');
    }

    public function tools()
    {
        return $this->belongsToMany(EntityRegistry::model('tool'), 'task_tool');
    }

    public function security_equipments()
    {
        return $this->belongsToMany(EntityRegistry::model('security_equipment'), 'security_equipment_task');
    }

    public function machineries()
    {
        return $this->belongsToMany(EntityRegistry::model('machinery'), 'machineries_task');
    }

    public function quarters()
    {
        return $this->belongsToMany(EntityRegistry::model('quarter'));
    }

    public function plants()
    {
        return $this->belongsToMany(EntityRegistry::model('plant'));
    }

    /**
     * In-module relationships — these stay as direct class references
     * because they reference sibling Tasks models in the same module.
     * No cross-module coupling needed.
     */
    public function supplies()
    {
        return $this->hasMany(SupplyTask::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
}
