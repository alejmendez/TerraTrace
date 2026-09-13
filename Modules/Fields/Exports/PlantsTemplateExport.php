<?php

namespace Modules\Fields\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class PlantsTemplateExport implements FromArray
{
    public function array(): array
    {
        return [
            ['Código', 'Hilera', 'Tipo de Planta', 'Fecha de plantación', 'Edad', 'Vivero de origen'],
            ['FL01AA12', 'A', 'Encina', date('Y-m-d'), '1', 'Jane Cooper'],
        ];
    }
}
