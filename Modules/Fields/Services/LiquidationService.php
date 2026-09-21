<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Liquidation;
use Modules\Fields\Models\LiquidationProduct;

class LiquidationService
{
    private const SEARCHABLE_COLUMNS = ['liquidation_number', 'delivery_date', 'importer.name'];

    /** Cache TTL for the available-years query (changes only when a new liquidation is added). */
    private const AVAILABLE_CACHE_TTL = 60;

    public function find(string|int $id): Liquidation
    {
        return Liquidation::findOrFail($id);
    }

    public function create(array $data): Liquidation
    {
        $liquidation = new Liquidation;
        $this->fill($liquidation, $data);
        $liquidation->save();

        $this->insertProducts($liquidation, $data['products'] ?? []);

        return $liquidation;
    }

    public function update(string|int $id, array $data): Liquidation
    {
        $liquidation = Liquidation::findOrFail($id);
        $this->fill($liquidation, $data);
        $liquidation->save();

        $this->syncProducts($liquidation, $data['products'] ?? []);

        return $liquidation;
    }

    public function delete(string|int $id): void
    {
        Liquidation::destroy($id);
    }

    /**
     * Cache-backed list of distinct years for which a liquidation
     * exists. Powers the year filter on the List page. Originally
     * LiquidationAvailableYears::call.
     */
    public function availableYears(): array
    {
        return Cache::remember('liquidation_available_years', self::AVAILABLE_CACHE_TTL, function () {
            return DB::table('liquidations')
                ->select('year')
                ->distinct()
                ->get()
                ->map(fn ($row) => ['value' => $row->year, 'text' => $row->year])
                ->all();
        });
    }

    public function list(array $params = []): mixed
    {
        $searchableColumns = self::SEARCHABLE_COLUMNS;

        $query = Liquidation::select(
            'liquidations.*',
            DB::raw('SUM(CASE WHEN category_products.is_commercial = true THEN liquidation_products.weight ELSE 0 END) as total_commercial'),
            DB::raw('SUM(CASE WHEN category_products.is_commercial = false THEN liquidation_products.weight ELSE 0 END) as total_not_commercial')
        )
            ->leftJoin('liquidation_products', 'liquidations.id', '=', 'liquidation_products.liquidation_id')
            ->leftJoin('category_products', 'liquidation_products.category_product_id', '=', 'category_products.id')
            ->groupBy('liquidations.id');

        $datatable = new PrimevueDatatables($params, $searchableColumns);

        return $datatable->of($query)->make();
    }

    public function collection(array $params = []): array
    {
        $query = Liquidation::query()
            ->select('liquidations.id', 'liquidations.liquidation_number', 'liquidations.delivery_date', 'liquidations.importer_id', 'liquidations.created_at')
            ->with(['importer:id,name'])
            ->withSum([
                'liquidationProducts as total_commercial' => function ($query) {
                    $query->whereHas('categoryProduct', fn ($q) => $q->where('is_commercial', true));
                },
            ], 'weight')
            ->withSum([
                'liquidationProducts as total_not_commercial' => function ($query) {
                    $query->whereHas('categoryProduct', fn ($q) => $q->where('is_commercial', false));
                },
            ], 'weight');

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('liquidations.liquidation_number', 'like', "%{$search}%")
                    ->orWhereHas('importer', function ($importerQuery) use ($search) {
                        $importerQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($params['importer_id'])) {
            $query->where('liquidations.importer_id', $params['importer_id']);
        }

        if (! empty($params['year'])) {
            $query->whereYear('liquidations.delivery_date', $params['year']);
        }

        $summary = [
            'liquidations' => (clone $query)->count(),
            'total_commercial' => (clone $query)->get()->sum('total_commercial'),
            'total_not_commercial' => (clone $query)->get()->sum('total_not_commercial'),
        ];

        $sort = in_array($params['sort'] ?? '', ['liquidation_number', 'delivery_date'], true)
            ? $params['sort']
            : 'delivery_date';
        $direction = ($params['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("liquidations.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($liquidation) {
            $liquidation->total_commercial = (float) ($liquidation->total_commercial ?? 0);
            $liquidation->total_not_commercial = (float) ($liquidation->total_not_commercial ?? 0);

            return $liquidation;
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem() ?? 0,
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Fill a Liquidation with the form payload. Shared between
     * create() and update() so both paths agree on the field shape
     * (the original Create and Update classes had identical
     * assignments copy-pasted).
     */
    private function fill(Liquidation $liquidation, array $data): void
    {
        $liquidation->date = $data['date'];
        $liquidation->field_id = $data['field_id']['value'];
        $liquidation->delivery_date = $data['delivery_date'];
        $liquidation->reception_date = $data['reception_date'];
        $liquidation->weight_with_earth = $data['weight_with_earth'];
        $liquidation->weight_washed = $data['weight_washed'];
        $liquidation->dollar_value = $data['dollar_value'];
        $liquidation->importer_id = $data['importer_id']['value'];
    }

    private function insertProducts(Liquidation $liquidation, array $products): void
    {
        foreach ($products as $productData) {
            $liquidationProduct = new LiquidationProduct;
            $liquidationProduct->liquidation_id = $liquidation->id;
            $liquidationProduct->category_product_id = $productData['category_product_id'];
            $liquidationProduct->weight = $productData['weight'];
            $liquidationProduct->price = $productData['price'];
            $liquidationProduct->save();
        }
    }

    /**
     * Idempotent sync of LiquidationProduct rows. Any existing row
     * not present in the payload is destroyed; rows with matching
     * ids update in place; rows without ids create. Same pattern
     * used for Batch and PlantDetail.
     */
    private function syncProducts(Liquidation $liquidation, array $products): void
    {
        $keptIds = [];

        foreach ($products as $productData) {
            if (! empty($productData['id'])) {
                $liquidationProduct = LiquidationProduct::find($productData['id']);
            }

            $liquidationProduct ??= new LiquidationProduct;

            $liquidationProduct->liquidation_id = $liquidation->id;
            $liquidationProduct->category_product_id = $productData['category_product_id'];
            $liquidationProduct->weight = $productData['weight'];
            $liquidationProduct->price = $productData['price'];
            $liquidationProduct->save();

            $keptIds[] = $liquidationProduct->id;
        }

        LiquidationProduct::where('liquidation_id', $liquidation->id)
            ->whereNotIn('id', $keptIds)
            ->delete();
    }
}
