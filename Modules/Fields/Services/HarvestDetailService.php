<?php

namespace Modules\Fields\Services;

use Illuminate\Support\Str;
use Modules\Fields\Models\Harvest;
use Modules\Fields\Models\HarvestDetail;

class HarvestDetailService
{
    /**
     * Quality grades accepted in the harvest-detail form. Used both
     * for the <select> in the UI and as the allow-list in the
     * FormRequest validator. The slug form (lowercase, hyphenated)
     * is what gets stored on the row.
     */
    private const QUALITIES = [
        'Extra',
        'First',
        'Big',
        'Second',
        'Small',
        'Extra small',
        'Mini',
        'Pieces',
        'Industrial',
    ];

    public function __construct(private readonly PlantService $plants) {}

    public function create(array $data): HarvestDetail
    {
        $plant = $this->plants->findByCode($data['plant_code']);

        $harvestDetail = new HarvestDetail;
        $harvestDetail->harvest_id = Harvest::latest()->first()->id;
        $harvestDetail->quarter_id = $plant->quarter_id;
        $harvestDetail->plant_id = $plant->id;
        $harvestDetail->quality = Str::slug($data['quality']['value'] ?? '');
        $harvestDetail->weight = $data['weight'];
        $harvestDetail->save();

        return $harvestDetail;
    }

    /**
     * Format the quality list for different consumers:
     *   - 'values'  => slugs only (used by the FormRequest validator)
     *   - 'select'  => [{value, text}, ...] (used by the <select>)
     *   - anything else => empty array (fail-safe default)
     */
    public function qualities(string $format): array
    {
        if ($format === 'values') {
            return array_map(fn ($quality) => Str::slug($quality), self::QUALITIES);
        }

        if ($format === 'select') {
            return array_map(fn ($quality) => ['value' => Str::slug($quality), 'text' => $quality], self::QUALITIES);
        }

        return [];
    }
}
