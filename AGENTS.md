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
| Iconografía | `@lucide/vue` (vía `<CollectionIcon name="...">`) |
| Datos auxiliares | Laravel Excel (importadores), Faker |
| Editor de texto rico | Quill 2.x + `quill-mention` (wrapper propio en `Core/Components/Form/VEditor.vue`) |

**Eliminado (no se usa, no se reintroduce):** PrimeVue, PrimeIcons, `@primevue/themes`,
`useToast`/`useConfirm` originales de PrimeVue. Axios sigue como dependencia global heredada
de Laravel; su uso queda restringido a features donde Inertia + `fetch` no llegan.

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
clases planas en `Services/` — ej. `Modules\Core\Services\CacheService`,
`Modules\Tasks\Services\NotifyTaskComment`.

Módulos actuales: `Auth`, `Core`, `Dashboard`, `Fields`, `Tasks`, `Users`.

---

## 4. Comunicación entre módulos

TerraTrace es un monolito modular: cada módulo es dueño de sus entidades y
de su acceso a datos. Cuando un módulo necesita información de otro, hay cuatro
niveles, cada uno apropiado para un caso. **El nivel más alto que aplique**;
nunca se mezcla un nivel con otro.

### 4.1 Datos planos / entidades nombradas → `EntityDispatcher`

Para cuando un módulo necesita una **lista plana** de otro módulo (combos,
selects, tablas de sólo lectura).

- El módulo productor expone un método en su entity-service
  (`FieldService::forSelect()`, `HarvestService::availableYears()`,
  etc.).
- El `Modules\Core\Registry\EntityDispatcher` mantiene un mapa
  `entity => [ServiceClass, method]` puramente — sólo entradas
  service-backed. El branch "static" / translation-driven que
  alguna vez existió para `scale_type`, `genders` y
  `is_commercial_options` fue retirado: ninguna ruta activa los
  usaba (los controllers ya tenían helpers locales o llamaban al
  service directo).
- Listas verdaderamente estáticas viven en el frontend como
  constantes en `Modules\Core\Resources\Constants/` (ej.
  `gender.js`, `scaleType.js`, `isCommercial.js`), con `labelKey`
  en vez de `text`. El consumidor las mapea a `{value, text}` con
  `__()` y se ahorra el roundtrip HTTP.
- El consumidor llama `EntityDispatcher::dispatch('field')` — desde
  un controlador HTTP, un service, o el endpoint legacy
  `Modules\Core\Http\Controllers\SelectsController`
  (`/api/selects/{entity}`).
- **Acoplamiento: por nombre de entidad.** El consumidor no conoce
  el modelo ni el service; sólo el slug.

```php
// Modules/Core/Registry/EntityDispatcher.php (mapa)
'field' => [FieldService::class, 'forSelect'],
'harvest_available_years' => [HarvestService::class, 'availableYears'],

// Consumidores
$fields = EntityDispatcher::dispatch('field');          // single
$batch  = EntityDispatcher::dispatchMany([...]);        // batch
```

Para añadir una nueva entidad: registrar el slug en el mapa del
dispatcher y exponer el método correspondiente en el service del
módulo dueño. **No** se toca `EntityRegistry` (ver §4.4).

### 4.2 Agregaciones / datos computados → `*StatsProvider`

Para cuando un módulo necesita **datos derivados** (contadores, sumas,
promedios, comparaciones) sobre los datos de otro.

- El módulo productor expone un `XxxStatsProvider` con métodos públicos.
- El consumidor lo inyecta por DI en su service y llama sólo lo que necesita.
- **Acoplamiento: por interfaz pública del provider.** El consumidor no toca
  queries, joins ni esquema del productor — sólo recibe valores calculados.

```php
// Modules/Dashboard/Services/Dashboard.php
public function __construct(
    private FieldsStatsProvider $fieldsStats,
    private TasksStatsProvider $tasksStats,
) {}

$data = [
    'harvest_data' => $this->fieldsStats->harvestStatsForField($field),
    'task_data'    => $this->tasksStats->taskCounters(),
];
```

Cada provider encapsula los joins y agregaciones que su módulo dueño conoce.
**Si una nueva pantalla necesita un contador cross-module, se añade un método
al `StatsProvider` correspondiente — no se importa el modelo desde el
consumidor.**

### 4.3 Eventos / notificaciones

Para cuando un módulo necesita notificar a destinatarios sobre cambios
(usuarios vía `Notification`, otros módulos vía eventos de Eloquent).

- El productor dispara el evento/notificación.
- Los suscriptores se registran en sus propios providers.
- **Acoplamiento: por evento.** Nadie sabe quién escucha.

```php
// Modules/Tasks/Services/NotifyTaskComment.php (transversal)
$user->notify(new TaskNotification([...]));
```

### 4.4 Modelo de Eloquent cross-module → `EntityRegistry::model()`

Para cuando un módulo necesita **la clase del modelo** de otro módulo
para resolver una relación Eloquent (`belongsTo`,
`belongsToMany`). El caso típico vive en
`Modules\Tasks\Models\Task::field()`:

```php
return $this->belongsTo(EntityRegistry::model('field'));
```

- El módulo productor registra la clase del modelo en su
  `ServiceProvider::register()` con
  `EntityRegistry::register('field', Field::class)`.
- El consumidor llama `EntityRegistry::model('field')` y obtiene el
  FQCN; nunca importa `Field` directamente.
- **Acoplamiento: por nombre.** El consumidor conoce el slug, no la
  clase concreta.

`EntityRegistry` también conserva `query()` y `reset()` para
compatibilidad con el legacy, pero su única vía viva hoy es
`model()`. La mitad "data-shape" de la API original
(`EntityRegistry::register(..., $factory)` + `ListEntity::call()`)
fue retirada: las listas planas ahora pasan por `EntityDispatcher`
(§4.1) y los services módulo-dueño.

### 4.5 Anti-patrones

| ❌ No | ✅ Sí |
|---|---|
| `use Modules\Fields\Models\Field` desde Dashboard | `FieldsStatsProvider::findField()` |
| Query directa cross-módulo en service propio | Llamar método del provider del módulo dueño |
| "Core services" centralizadores (`Core\FieldsQueries`) | El módulo dueño expone su propio provider |
| Importar `XxxService` completo cuando sólo se quiere una vista parcial | Usar el provider específico |
| `EntityRegistry::register('field', Field::class, $closure)` para data-shape | `EntityDispatcher::dispatch('field')` → método en `FieldService` |
| `ListEntity::call('field')` (legacy) | Inyectar `FieldService` directamente en el consumer |

### 4.6 Excepciones controladas donde SÍ se cruzan módulos

- **Events de framework** con modelos en el payload (`Registered(User $user)`).
- **El provider mismo** — sí importa su modelo (es interno al productor).
- **Tests de integración** cross-module.
- **Cross-module auth**: Auth importa `Modules\Users\Models\User` porque el
  dominio Auth requiere el modelo del dominio Users. Legítimo.
- **`EntityRegistry::model()`** desde relaciones cross-module (ver §4.4):
  el consumidor importa el registro, no el modelo. Es la única vía
  legítima para resolver un FQCN sin acoplar al modelo concreto.

---

## 5. Convenciones backend

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
página ya está migrada al nuevo flujo (ver §7).

---

## 6. Convenciones frontend

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

## 7. Migración PrimeVue → Collection UI

**Estado (2026-Q3):** completa. Las 5 deps de PrimeVue
(`primevue`, `primeicons`, `@primevue/themes`) fueron removidas de
`package.json` y de `node_modules`. El último PrimeVue plugin init fue
borrado en `Modules/Core/Resources/Libs/prime.js` (junto con la carpeta
`PrimePresents/` que hospedaba los 5 presets de tema) y el theme
switcher de 5 colores de `MenuUser.vue` se reemplazó por un toggle
light/dark puro Tailwind. El CSS de los componentes PrimeVue
(`.p-button.p-component`, `.p-datatable`, `.p-accordion*`, `.p-toast`,
`.swal2-modal`) y las CSS vars de tema (`--p-primary-*`) se borraron de
`resources/css/app.css`.

**Sustituciones finales:**

| Antes | Ahora |
|---|---|
| `primevue/datatable` + `Column` + filtros | `Core/Components/Table/Datatable.vue` eliminado; tablas usan HTML + Tailwind + `<CollectionPagination>` |
| `primevue/editor` | `Core/Components/Form/VEditor.vue` envuelve Quill 2.0.2 + `quill-mention` directo |
| `useToast()` / `useConfirm()` de PrimeVue | composables propios en `Core/Composables/` (mismo shape, sin deps) |
| `--p-primary-500/300/600` | hex Tailwind: `text-[#17663a]` / `hover:text-[#86c798]` / `dark:hover:text-[#105534]` |
| `pi pi-*` (PrimeIcons) | `<CollectionIcon name="...">` (lucide-vue) |
| 5 temas PrimeVue (`Apple`/`Cobalt`/etc.) | toggle light/dark puro CSS (`html.dark`) |

**Convenciones del editor Quill (VEditor.vue):** el binding `v-model`
es un HTML string. La mención usa `mentionDenotationChars: ['@']` y el
`source` filtra por `option.text` de la prop `options`
(`{ value, text }[]`). El wrapper maneja el ciclo Quill ↔ Vue con un
flag `suppressNextSync` para que `dangerouslyPasteHTML` no re-dispare
el watcher y haga saltar el cursor.

**Notas para consumidores de VEditor:** el componente `CollectionButton`
no tiene prop `icon` (era un residuo heredado de PrimeVue Button). Si
necesitas icono, usa el slot por defecto: `<CollectionButton>
<CollectionIcon name="..."/> Label</CollectionButton>`.

---

## 8. Lo que NO se hace en este proyecto

- ❌ Reintroducir PrimeVue, PrimeIcons o `@primevue/themes`.
- ❌ Usar la CSS class `pi pi-*` (PrimeIcons); usar `<CollectionIcon name="...">`.
- ❌ Usar `--p-primary-*` o cualquier CSS var de tema PrimeVue; usar hex Tailwind
  directo (`text-[#17663a]`, `border-[#17663a]`, etc.).
- ❌ Usar el wrapper `primevue/editor`; el editor de texto rico es
  `Core/Components/Form/VEditor.vue` (Quill directo).
- ❌ Añadir `axios` como dependencia de feature (sólo queda el global
  heredado de Laravel; está en desuso).
- ❌ Devolver `JsonResponse` ad-hoc para "consumir desde Vue con fetch";
  usar Inertia props.
- ❌ Crear servicios "manager" multi-acción; una clase por acción.
- ❌ Escribir queries para selects en el controlador; usar `ListEntity::call()`.
- ❌ Commits que mezclen migración de UI con refactor de backend no
  relacionado.

---

## 9. Verificación previa a commit

Antes de pedir review:

- `composer pint` (o el formateador configurado) en archivos modificados.
- Si la migración toca rutas: `php artisan route:list` para confirmar.
- Si toca permisos: smoke test del CRUD correspondiente con un usuario
  que tenga y uno que no tenga el permiso.
- Capturar un screenshot corto de la vista migrada si cambia layout.
