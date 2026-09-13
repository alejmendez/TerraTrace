<?php

namespace Modules\Fields\Imports;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Fields\Models\Plant;
use Modules\Fields\Models\PlantType;

class PlantsImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    protected $line = 0;

    protected $unprocessedRecords = [];

    protected $quarter_id;

    protected $plant_types;

    private $rowCount = 0;

    public function __construct($quarter_id)
    {
        $this->quarter_id = $quarter_id;
        $this->plant_types = PlantType::all()->pluck('id', 'slug')->toArray();
    }

    public function model(array $row)
    {
        $this->line++;
        $plant = new Plant;

        $plant->quarter_id = $this->quarter_id;
        $plant->code = $row['codigo'];
        $plant->row = $row['hilera'];
        $plant->age = $row['edad'];
        $plant->plant_type_id = $this->getPlantTypeIdByName($row['tipo_de_planta']);
        $plant->planned_at = $row['fecha_de_plantacion'];
        $plant->nursery_origin = $row['vivero_de_origen'];

        $count = Plant::where([
            'quarter_id' => $plant->quarter_id,
            'code' => $plant->code,
            'row' => $plant->row,
            'age' => $plant->age,
            'plant_type_id' => $plant->plant_type_id,
            'planned_at' => $plant->planned_at,
            'nursery_origin' => $plant->nursery_origin,
        ])->count();

        if ($count > 0) {
            $this->unprocessedRecords[] = [
                'line' => $this->line,
                'code' => $row['codigo'],
            ];

            return null;
        }

        $this->rowCount++;

        return $plant;
    }

    public function getPlantTypeIdByName($name)
    {
        $slug = Str::slug($name);

        if (isset($this->plant_types[$slug])) {
            $plant_type_id = $this->plant_types[$slug];
        } else {
            $plant_type = new PlantType;
            $plant_type->name = $name;
            $plant_type->slug = $slug;
            $plant_type->save();

            $this->plant_types[$slug] = $plant_type->id;
            $plant_type_id = $plant_type->id;
        }

        return $plant_type_id;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', Rule::unique('plants', 'code')],
            'hilera' => 'required|max:2',
            'edad' => 'required|max:3',
            'tipo_de_planta' => 'required|max:20',
            'fecha_de_plantacion' => 'required|date_format:Y-m-d',
            'vivero_de_origen' => 'required|max:80',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'codigo.unique' => 'El código de planta ":input" ya existe y no será creado nuevamente.',
            'hilera.max' => 'La hilera ":input" es inválida, debe tener un maximo de 2 caracteres.',
            'edad.max' => 'La edad ":input" es inválida, debe tener un maximo de 3 caracteres.',
            'tipo_de_planta.max' => 'El tipo de planta ":input" es inválido, debe tener un maximo de 20 caracteres.',
            'fecha_de_plantacion.date_format' => 'La fecha de plantacion ":input" es inválida, debe tener formato Y-m-d.',
            'vivero_de_origen.max' => 'El vivero de origen ":input" es inválido, debe tener un maximo de 80 caracteres.',
        ];
    }

    public function prepareForValidation($data, $index)
    {
        $data['codigo'] = strtoupper(trim($data['codigo'] ?? ''));

        return $data;
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }

    public function getUnprocessedRecords()
    {
        return $this->unprocessedRecords;
    }

    public function getNumberOfUnprocessedRecords()
    {
        return count($this->unprocessedRecords);
    }
}
