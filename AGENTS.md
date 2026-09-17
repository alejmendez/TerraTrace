# AGENTS.md

Contexto operativo para agentes que trabajen sobre **TerraTrace**. Léelo antes
de modificar código, abrir PRs o proponer cambios de arquitectura.

---

## 1. Identidad del proyecto

TerraTrace es una plataforma de gestión agrícola que modela el flujo
operativo: **Predio → Cuartel → Planta → Cosecha → Lote → Inventario →
Liquidación**, además de tareas, recursos y notificaciones.

Dominio: empresas agrícolas chilenas. El idioma de la UI es **español**; los
identificadores de código, migraciones y commits técnicos van en **inglés**
cuando aplica.

---

## 2. Stack

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Eloquent, Sanctum, Spatie permissions |
| Runtime | Laravel Octane |
| Frontend | Vue 3, Inertia 3, Tailwind 4 (Vite), Ziggy |
| Iconografía | `@lucide/vue` (NO `primeicons`/`material-symbols` en código nuevo) |
| Datos auxiliares | Laravel Excel (importadores), Faker |

**NO se usan (migración en curso):** PrimeVue (componentes y `useToast`/
`useConfirm`), Axios como dependencia (sustituido por Inertia + `fetch`
nativo donde Inertia no llega).

---

## 3. Estructura modular

El código vive bajo `Modules/<Context>/`. Cada módulo expone cuatro bloques:

```
Modules/<Context>/
├── Http/
│   ├── Controllers/   → un controlador por recurso
│   ├── Requests/      → FormRequests por create/update
│   └── Resources/     → JsonResource para show/edit
├── Services/
│   └── <Entity>Service.php   ← clase única por entidad, métodos de instancia
├── Models/
└── Resources/         ← recursos frontend (Vue)
    ├── Pages/<Ent>/{List,Create,Edit,Show}.vue
    ├── Services/<Ent>Service.js
    └── Components/    ← componentes propios del módulo
```

**Convención de servicios:** una clase manager-style por entidad, con todos
sus métodos de acción expuestos como métodos de instancia
(`create`, `update`, `delete`, `find`, `list`, `collection`, etc.).
Se inyecta vía constructor del controlador; nunca se llama con `::call()`
estático. Esto evita el ruido de una clase-archivo por acción y centraliza
la lógica CRUD por dominio.

Ejemplo:

```php
class PlantService
{
    public function list(array $params = []): mixed { /* … */ }
    public function find(int $id): Plant { /* … */ }
    public function findByCode(string $code): ?Plant { /* … */ }
    public function create(array $data): Plant { /* … */ }
    public function update(int $id, array $data): Plant { /* … */ }
    public function delete(int $id): void { /* … */ }
    public function createNote(array $data): PlantDetail { /* … */ }
}

class PlantsController
{
    public function __construct(private PlantService $plants) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json($this->plants->list($request->all()));
    }
}
```

Los servicios transversales (no asociados a una sola entidad) viven como
clases planas en `Services/` — ej. `Modules\Core\Services\ListEntity`,
`Modules\Core\Services\CacheService`, `Modules\Tasks\Services\NotifyTaskComment`.

Módulos actuales: `Auth`, `Core`, `Dashboard`, `Fields`, `Tasks`, `Users`.

---

## 4. Convenciones backend

### Permisos

Todo controlador usa el trait `HasPermissionMiddleware` y declara permisos
del estilo `<entidad>.<acción>` (`fields.create`, `fields.destroy`, etc.).

### Selects / combos

Para alimentar `<select>` desde el backend se usa el helper:

```php
'fields' => ListEntity::call('field'),
```

No escribir queries ad-hoc en controladores para poblar combos.

### Listado de recursos

Las páginas `List.vue` consumen el resultado del método `List<Entity>::call()`
o `List<Entity>::collection()` del servicio. Por convención:

- `call($params)` → formato legacy PrimeVue Datatable (`dt_params`), mantener
  sólo donde aún queden vistas sin migrar.
- `collection($params)` → formato moderno, devuelve `{ items, meta, summary }`
  para la UI `Collection*`.

No se devuelve `JsonResponse` desde el controlador sin verificar antes si la
página ya está migrada al nuevo flujo (ver §6).

---

## 5. Convenciones frontend

### Aliases

`config/modules.php` es la **única fuente de verdad** de qué módulos existen.
Los aliases de import se generan/regeneran con:

```bash
php artisan modules:sync          # aplica cambios
php artisan modules:sync --dry-run # revisa antes de aplicar
```

El comando reescribe dos archivos derivados (no editarlos a mano):

- `jsconfig.json` → `compilerOptions.paths` con `@<Modulo>/*` apuntando a
  `./Modules/<Modulo>/Resources/*`. Otros paths (ej. `ziggy-js`) se preservan.
- `vite.config.js` → array `const modules = [...]` que se usa para generar
  los aliases de Vite (`@<Modulo>`) y las rutas de i18n (`additionalLangPaths`).

Tras añadir un módulo a `config/modules.php`, correr `php artisan modules:sync`
es obligatorio para que el bundler y el editor lo vean. El orden de los
providers en `config/modules.php` define el orden en ambos archivos derivados.

Usar siempre el alias (`@Fields/...`), nunca rutas relativas entre módulos.

### Layout y header

- Layout: `@Core/Layouts/AuthenticatedLayout.vue`.
- Header: preferir `@Core/Components/Collection/CollectionPageHeader.vue`
  en código nuevo. `HeaderCrud` queda para vistas aún no migradas.

### Iconografía

Usar `@Core/Components/Collection/CollectionIcon.vue` con nombres del mapa
interno (`add`, `landscape`, `potted_plant`, `grid_view`, `map`, etc.).
**NO** introducir nuevas dependencias de iconos (`material-symbols`,
`primeicons` solo dentro de `primevue/*`).

### Patrón de datos en páginas `List.vue`

**Convención objetivo (migración en curso):**

- Estado de filtros / paginación / orden vive en **query params** de la URL.
- El componente lee los datos desde las props de Inertia
  (`defineProps({ records: Object, ... })`).
- Refrescos parciales con `router.reload({ only: ['records', 'meta', 'summary'], preserveState: true, preserveScroll: true })`.
- Eliminaciones vía `router.delete(route('<ent>.destroy', id), { ... })`.
- Navegación entre páginas con `<Link>` (no `window.location`).

**Patrón transitorio (vista actual de Fields/Plants/Quarters):**

- Endpoint JSON dedicado (`?collection=1`) + composable
  `useCollection` + `axios.get`. Es aceptable mientras se valida, pero
  está marcado para reemplazarse por el patrón objetivo cuando la pantalla
  siguiente se migre con la convención final.

### Formularios Create/Edit

Usar `useForm` de `@inertiajs/vue3`. POST/PUT a `route('<ent>.store')` /
`route('<ent>.update', id)`. **No** usar `axios.post` ni `fetch` para
submit.

### Rutas API por módulo

Cada módulo puede declarar rutas API en `Modules/<Modulo>/Routes/api.php`.
`ModulesServiceProvider` las carga automáticamente con el middleware group
`api` y el prefijo URL `/api`. Declarar las rutas **sin** el prefijo:

```php
// Modules/Auth/Routes/api.php
Route::post('auth/sign_in', [AuthenticatedApiController::class, 'store']);
// → POST /api/auth/sign_in (middleware: api)

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthenticatedApiController::class, 'user']);
    // → GET /api/user (middleware: api, auth:sanctum)
});
```

**No** poner rutas API dentro de `Routes/web.php` ni envolverlas en
`Route::prefix('api')` — el loader ya aplica ambos.

### Composables y componentes compartidos

Cualquier primitiva reutilizable va en `Modules/Core/Resources/Composables/`
o `Modules/Core/Resources/Components/Collection/`. Antes de crear una nueva,
verificar que no exista ya.

---

## 6. Migración PrimeVue → Collection UI

**Estado:** todas las vistas `List.vue` migradas (Fields, Plants, Quarters,
Dogs, Users, Tools, SecurityEquipments, Machineries, Owners, PlantTypes,
Tasks, Harvests, Batches, Liquidations, Importers, CategoryProducts).
Componentes base disponibles en `Modules/Core/Resources/Components/Collection/`.

**Reglas:**

1. **Una vista por commit.** No migrar dos `List.vue` en el mismo PR.
2. **Conservar el contrato backend.** `List<Entity>::call()` queda mientras
   coexistan vistas legacy; `List<Entity>::collection()` se añade cuando
   se migra la vista correspondiente.
3. **Conservar permisos.** No eliminar `can('<ent>.<acción>')` al migrar;
   sólo cambia el componente que renderiza el botón.
4. **No reintroducir PrimeVue.** Si una migración parece requerir un
   componente de PrimeVue, evaluar primero si tiene equivalente
   `Collection*` o si vale la pena crearlo.
5. **Eliminar dependencia sólo al final.** `PrimeVue` permanece en
   `package.json` hasta que la última vista legacy (Show pages,
   HarvestTable.vue, etc.) se haya migrado y borrado.

**Próximas tandas pendientes:**

- **Show pages** con PrimeVue (`Quarters/Show`, `Plants/Show`,
  `Fields/Show`, `Tasks/Show`, `Harvests/Show`, `Batches/Show`,
  `Liquidations/Show`, `Harvests/Show`, etc.).
- **`HarvestTable.vue`** (componente usado por Quarters/Fields Show).
- **Componentes `Form*.vue`** (FormDog, FormPlant, etc.) que aún
  importan PrimeVue `InputText`, `InputNumber`, etc. vía `VInput` y
  `VInputNumber` (transitivo).
- **`HeaderCrud` / `CardSection`** y otros componentes compartidos
  que aún usan PrimeVue.
- **`Tasks/Comments`** (componente que renderiza PrimeVue `Timeline`).

---

## 7. Lo que NO se hace en este proyecto

- ❌ Añadir PrimeVue, PrimeIcons, Material Symbols en código nuevo.
- ❌ Añadir `axios` como dependencia de feature (sólo queda el global
  heredado de Laravel; está en desuso).
- ❌ Devolver `JsonResponse` ad-hoc para "consumir desde Vue con fetch";
  usar Inertia props.
- ❌ Crear servicios "manager" multi-acción; una clase por acción.
- ❌ Escribir queries para selects en el controlador; usar `ListEntity::call()`.
- ❌ Commits que mezclen migración de UI con refactor de backend no
  relacionado.

---

## 8. Verificación previa a commit

Antes de pedir review:

- `composer pint` (o el formateador configurado) en archivos modificados.
- Si la migración toca rutas: `php artisan route:list` para confirmar.
- Si toca permisos: smoke test del CRUD correspondiente con un usuario
  que tenga y uno que no tenga el permiso.
- Capturar un screenshot corto de la vista migrada si cambia layout.
