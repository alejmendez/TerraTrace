<?php

return [
    'titles' => [
        'entity_breadcrumb' => 'Plantas',
        'create' => 'Crear Planta',
        'edit' => 'Editar Planta',
        'bulk' => 'Nuevo carga masiva de plantas',
        'show' => 'Detalle de la Planta: :name',
        'add_variables' => 'Agregar Variables',
    ],
    'table' => [
        'name' => 'Planta',
        'quarter_id' => 'Cuartel',
        'field_id' => 'Campo',
        'location' => 'Ubicación',
        'type' => 'Tipo de planta',
        'age' => 'Edad',
        'manager' => 'Responsable',
    ],
    'form' => [
        'name' => [
            'label' => 'Nombre del planta',
        ],
        'plant_type_id' => [
            'label' => 'Tipo de planta',
        ],
        'age' => [
            'label' => 'Edad de la planta',
        ],
        'planned_at' => [
            'label' => 'Fecha de plantación',
        ],
        'nursery_origin' => [
            'label' => 'Vivero de origen',
        ],
        'code' => [
            'label' => 'Código de la planta',
        ],
        'quarter_id' => [
            'label' => 'Perteneciente al cuartel',
        ],
        'row' => [
            'label' => 'Hilera',
        ],
        'field_id' => [
            'label' => 'Perteneciente al campo',
        ],
        'blueprint' => [
            'label' => 'Seleccione sus planos',
        ],
        'note' => [
            'label' => 'Nota',
        ],
    ],
    'show' => [
        'file' => [
            'field' => 'Perteneciente al campo',
            'quarter' => 'Perteneciente al cuartel',
            'plant_type' => 'Tipo de planta',
            'age' => 'Edad',
            'planned_at' => 'Fecha de plantación',
            'responsible' => 'Responsable',
        ],
        'logs' => [
            'title' => 'Bitácora',
        ],
        'statistics' => [
            'title' => 'Estadísticas',
        ],
        'tabs' => [
            'file' => 'Ficha',
            'logs' => 'Bitácora',
            'harvest' => 'Cosecha',
            'statistics' => 'Estadísticas',
        ],
    ],
    'sections' => [
        'location' => 'Ubicación',
        'blueprint' => 'Planos',
    ],
    'bulk' => [
        'form' => [
            'field_id' => 'Campo',
            'quarter_id' => 'Cuartel',
        ],
    ],
];
