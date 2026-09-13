<?php

namespace Modules\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Core\Models\Menu;
use Modules\Core\Models\Module;
use Modules\Users\Models\User;

/**
 * Service for handling menu generation and management in the application
 */
class MenuService
{
    private const CACHE_TTL_MODULES = 600; // 10 minutes

    private const CACHE_TTL_MENUS = 600; // 10 minutes

    private const CACHE_KEY_MODULES = 'modules';

    private const CACHE_KEY_MENUS = 'menus';

    private ?User $user;

    private Request $request;

    private array $permissions = [];

    private Collection $modules;

    private Collection $menus;

    /**
     * Service constructor
     */
    public function __construct(Request $request, ?User $user = null)
    {
        $this->request = $request;
        $this->user = $user;

        if ($this->user) {
            $this->initializeUserData();
        }
    }

    /**
     * Gets the complete menu for the current user
     */
    public function getMenu(): array
    {
        if (! $this->user) {
            return [];
        }

        return $this->getRootElements()
            ->map(fn ($item) => $this->getMenuData($item))
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Initializes user data
     */
    private function initializeUserData(): void
    {
        $this->permissions = CacheService::getUserPermissions($this->user)->toArray();
        $this->modules = $this->getModules();
        $this->menus = $this->getMenus();
    }

    /**
     * Gets the data for a menu item
     */
    private function getMenuData(Menu $item): ?array
    {
        $children = $this->menus->where('parent_id', $item->id);

        if ($children->isEmpty()) {
            return $this->getLeafMenuItem($item);
        }

        return $this->getParentMenuItem($item, $children);
    }

    /**
     * Gets the data for a leaf menu item
     */
    private function getLeafMenuItem(Menu $item): ?array
    {
        if (! isset($this->modules[$item->module_id]) || ! in_array($item->link, $this->permissions)) {
            return null;
        }

        return [
            'text' => __($item->text),
            'link' => route($item->link),
            'icon' => $item->icon,
            'active' => $this->request->routeIs($item->active_with),
        ];
    }

    /**
     * Gets the data for a parent menu item
     */
    private function getParentMenuItem(Menu $item, Collection $children): ?array
    {
        $childrenData = $children->map(fn ($child) => $this->getMenuData($child))
            ->filter()
            ->values();

        if ($childrenData->isEmpty()) {
            return null;
        }

        return [
            'text' => __($item->text),
            'children' => $childrenData,
        ];
    }

    /**
     * Gets active modules from cache
     */
    private function getModules(): Collection
    {
        return cache()->remember(
            self::CACHE_KEY_MODULES,
            self::CACHE_TTL_MODULES,
            fn () => Module::where('is_active', true)->pluck('name', 'id')
        );
    }

    /**
     * Gets menu items from cache
     */
    private function getMenus(): Collection
    {
        return cache()->remember(
            self::CACHE_KEY_MENUS,
            self::CACHE_TTL_MENUS,
            fn () => Menu::orderBy('order')->get()
        );
    }

    /**
     * Gets root menu elements
     */
    private function getRootElements(): Collection
    {
        return $this->menus->whereNull('parent_id');
    }
}
