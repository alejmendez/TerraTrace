<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\CacheService;
use Modules\Users\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-permissions {--dry-run : Report what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions (idempotent: upsert only, never delete)';

    protected $defaultGuard = 'web';

    protected $entities = [
        'batches',
        'category_products',
        'dogs',
        'fields',
        'harvests',
        'importers',
        'liquidations',
        'machineries',
        'owners',
        'plant_types',
        'plants',
        'quarters',
        'security_equipments',
        'tasks',
        'tools',
        'users',
    ];

    protected $defaultActions = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

    protected $permissions = [];

    protected $roles = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->create_roles();
        $this->declare_permissions();

        $this->save_permissions();

        $this->permissions = collect($this->permissions);

        $allPermissions = $this->permissions->flatten();

        if ($dryRun) {
            $this->info(sprintf(
                '[dry-run] Would upsert %d permissions; would grant %d to super_admin, %d to administrator.',
                $allPermissions->count(),
                $allPermissions->count(),
                $allPermissions->count(),
            ));

            return self::SUCCESS;
        }

        // Roles that should hold every declared permission.
        $this->roles['super_admin']->givePermissionTo($allPermissions->toArray());
        $this->roles['administrator']->givePermissionTo($allPermissions->toArray());

        // Roles with a curated subset. givePermissionTo is additive: existing
        // grants (including any manual ones) are preserved; only the declared
        // permissions are added on top.
        $this->roles['technician']->givePermissionTo([
            'dashboard.index',
            'fields.index',
            'fields.show',
            'quarters.index',
            'quarters.show',
            'plants.index',
            'plants.show',
            'harvests.index',
            'harvests.create',
            'harvests.store',
            'harvests.show',
            'harvests.edit',
            'harvests.update',
            // 'harvests.destroy',
            'liquidations.index',
            'liquidations.create',
            'liquidations.store',
            'liquidations.show',
            // 'liquidations.edit',
            // 'liquidations.update',
            // 'liquidations.destroy',
            'selects.index',
            'selects.multiple',
            'graphs.index',
            'quarters.plants',
            ...$this->permissions['plants.details'],
            ...$this->permissions['batches'],
            ...$this->permissions['harvests_details'],
            ...$this->permissions['tasks'],
            ...$this->permissions['machineries'],
            ...$this->permissions['tools'],
            ...$this->permissions['security_equipments'],
        ]);

        $this->roles['farmer']->givePermissionTo([
            ...$this->permissions['harvests_details'],
            ...$this->permissions['tasks'],
        ]);

        $users = User::all();
        foreach ($users as $user) {
            CacheService::clearUserCache($user);
        }

        $this->info(sprintf(
            'Done. %d permissions in scope; cache invalidated for %d users.',
            $allPermissions->count(),
            $users->count(),
        ));

        return self::SUCCESS;
    }

    /**
     * Build the in-memory permissions manifest. Pure declaration, no I/O.
     */
    private function declare_permissions(): void
    {
        foreach ($this->entities as $entity) {
            foreach ($this->defaultActions as $action) {
                $this->create_permission($entity, $action);
            }
        }

        $this->create_permission('dashboard', 'index');
        $this->create_permission('bulk', 'index');

        $this->create_permission('harvests', 'download.bulk.template');
        $this->create_permission('harvests', 'create.bulk');
        $this->create_permission('harvests', 'store.bulk');

        $this->create_permission('plants', 'download.bulk.template');
        $this->create_permission('plants', 'create.bulk');
        $this->create_permission('plants', 'store.bulk');
        $this->create_permission('plants', 'notes.store');
        $this->create_permission('plants.details', 'index');
        $this->create_permission('plants.details', 'store');
        $this->create_permission('plants.details', 'by_quarter');
        $this->create_permission('plants.details', 'by_field');

        $this->create_permission('harvests_details', 'create');
        $this->create_permission('harvests_details', 'store');
        $this->create_permission('harvests_details', 'find_by_code');
        $this->create_permission('selects', 'index');
        $this->create_permission('selects', 'multiple');
        $this->create_permission('tasks', 'comments.store');
        $this->create_permission('tasks', 'comments.update');
        $this->create_permission('tasks', 'comments.destroy');
        $this->create_permission('graphs', 'index');

        $this->create_permission('quarters', 'plants');
        $this->create_permission('quarters', 'plants.update.position');
    }

    public function create_roles()
    {
        $this->roles = [
            'farmer' => 'Agricultor',
            'technician' => 'Técnico',
            'administrator' => 'Administrador',
            'super_admin' => 'Super Admin',
            'app_assistant' => 'Ayudante APP',
            'external_advisor' => 'Asesor Externo',
        ];

        foreach ($this->roles as $key => $name) {
            $rol = Role::where('name', $name)->first();
            if (! $rol) {
                $rol = Role::create(['name' => $name]);
            }
            $this->roles[$key] = $rol;
        }
    }

    public function create_permission($entity, $action)
    {
        $permission = $entity.'.'.$action;

        if (! isset($this->permissions[$entity])) {
            $this->permissions[$entity] = [];
        }

        $this->permissions[$entity][] = $permission;
    }

    public function save_permissions()
    {
        // Merge in any module-level permission manifest under
        // Modules/<Name>/Config/permissions.php. This is the contract
        // for new modules created with `php artisan modules:make`:
        // declare custom permission strings there and SyncPermissions
        // picks them up automatically without editing this file.
        foreach (config('modules.providers', []) as $provider) {
            if (! preg_match('/Modules\\\\([A-Z][A-Za-z0-9_]*)\\\\Providers\\\\/', $provider, $m)) {
                continue;
            }
            $path = base_path("Modules/{$m[1]}/Config/permissions.php");
            if (! is_file($path)) {
                continue;
            }
            $manifest = require $path;
            if (! is_array($manifest)) {
                continue;
            }
            foreach ($manifest as $name) {
                if (is_string($name) && $name !== '') {
                    $this->permissions['__modules__'][] = $name;
                }
            }
        }

        $existingPermissions = Permission::pluck('name')->toArray();
        $permissionToCreate = [];
        $now = now()->toDateTimeString();
        foreach ($this->permissions as $entity => $permissions) {
            foreach ($permissions as $permission) {
                if (! in_array($permission, $existingPermissions)) {
                    $permissionToCreate[] = [
                        'name' => $permission,
                        'guard_name' => $this->defaultGuard,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if ($permissionToCreate !== []) {
            Permission::insert($permissionToCreate);
        }
    }
}
