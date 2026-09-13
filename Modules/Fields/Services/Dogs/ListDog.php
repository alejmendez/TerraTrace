<?php

namespace Modules\Fields\Services\Dogs;

use Modules\Core\Services\PrimevueDatatables;
use Modules\Fields\Models\Dog;

class ListDog
{
    public static function call($params = [])
    {
        $searchableColumns = ['name', 'birthdate', 'field.name', 'gender', 'breed', 'veterinary', 'couple.full_name'];

        $query = Dog::query();

        $datatable = new PrimevueDatatables($params, $searchableColumns);
        $dogs = $datatable->of($query)->make();

        $dogs->map(function ($dog) {
            $dog->gender = trans('dog.form.gender.options.'.($dog->gender === 'M' ? 'male' : 'female'));
        });

        return $dogs;
    }

    public static function collection(array $params = []): array
    {
        $query = Dog::query()
            ->select('dogs.id', 'dogs.name', 'dogs.birthdate', 'dogs.gender', 'dogs.breed', 'dogs.veterinary', 'dogs.field_id', 'dogs.couple_id')
            ->with([
                'field:id,name',
                'couple:id,full_name',
            ]);

        $search = trim($params['q'] ?? '');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('dogs.name', 'like', "%{$search}%")
                    ->orWhere('dogs.breed', 'like', "%{$search}%")
                    ->orWhere('dogs.veterinary', 'like', "%{$search}%")
                    ->orWhereHas('field', function ($fieldQuery) use ($search) {
                        $fieldQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('couple', function ($coupleQuery) use ($search) {
                        $coupleQuery->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($params['field_id'])) {
            $query->where('dogs.field_id', $params['field_id']);
        }

        if (!empty($params['gender'])) {
            $query->where('dogs.gender', $params['gender']);
        }

        if (!empty($params['couple_id'])) {
            $query->where('dogs.couple_id', $params['couple_id']);
        }

        $summary = [
            'dogs' => (clone $query)->count(),
            'fields' => (clone $query)->distinct('dogs.field_id')->count('dogs.field_id'),
            'couples' => (clone $query)->whereNotNull('dogs.couple_id')->distinct('dogs.couple_id')->count('dogs.couple_id'),
        ];

        $sort = in_array($params['sort'] ?? '', ['name', 'breed', 'veterinary', 'birthdate'], true)
            ? $params['sort']
            : 'name';
        $direction = ($params['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) ($params['per_page'] ?? 12), 1), 24);
        $page = max((int) ($params['page'] ?? 1), 1);

        $paginator = $query
            ->orderBy("dogs.{$sort}", $direction)
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($paginator->items())->map(function ($dog) {
            $dog->gender = trans('dog.form.gender.options.'.($dog->gender === 'M' ? 'male' : 'female'));

            return $dog;
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
}
