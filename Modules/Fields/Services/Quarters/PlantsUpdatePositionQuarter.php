<?php

namespace Modules\Fields\Services\Quarters;

use Illuminate\Support\Facades\DB;
use Modules\Fields\Models\Plant;

class PlantsUpdatePositionQuarter
{
    public static function call(string $quarter_id, array $data): void
    {
        $batchSize = 500;

        collect($data)->chunk(500)->each(function ($chunk) use ($quarter_id) {
            DB::transaction(function () use ($chunk, $quarter_id) {
                foreach ($chunk as $plantData) {
                    $code = $plantData[0];
                    Plant::where('code', $code)->where('quarter_id', $quarter_id)->update(['position' => "$plantData[1],$plantData[2]"]);
                }
            });
        });
    }
}
