<?php

return [
    'titles' => [
        'entity_breadcrumb' => 'Cosechas',
        'create' => 'Crear Cosecha',
        'edit' => 'Editar Cosecha Semana :week Batch :batch',
        'bulk' => 'Nuevo carga masiva de cosechas',
    ],
    'table' => [
        'date' => 'Fecha',
        'week' => 'Semana',
        'batch' => 'Batch',
        'weight' => 'Peso',
        'count_details' => 'Unidades',
        'field' => 'Campos',
        'quarter' => 'Cuarteles',
        'responsible' => 'Responsable',
    ],
    'table_filters' => [
        'field' => 'Campo',
        'quarter' => 'Cuartel',
        'year' => 'Año de cosecha',
    ],
    'table_data' => [
        'date' => 'Semana :week - :year',
    ],
    'buttons' => [
        'add_detail' => 'Añadir otro',
        'change_distribution' => 'Cambiar distribución',
    ],
    'form' => [
        'date' => [
            'label' => 'Fecha',
            'label_rendered' => 'Semana',
            'renderText' => 'Semana :week - :start al :end',
        ],
        'batch' => [
            'label' => 'Batch',
            'renderText' => 'Semana :week - Batch :batch',
        ],
        'details' => [
            'plant_code' => [
                'label' => 'Planta',
            ],
            'quality' => [
                'label' => 'Calidad',
            ],
            'weight' => [
                'label' => 'Peso (grs)',
            ],
        ],
        'quarter_ids' => [
            'label' => 'Cuarteles',
        ],
        'dog_id' => [
            'label' => 'Perro',
        ],
        'farmer_id' => [
            'label' => 'Agricultor',
        ],
        'assistant_id' => [
            'label' => 'Ayudante',
        ],
        'note' => [
            'label' => 'Nota',
        ],
        'comments' => [
            'label' => 'Comentarios',
        ],
        'weight' => [
            'label' => 'Peso total',
        ],
    ],
    'sections' => [
        'harvest' => 'Registro Manual Semana :week Batch :batch',
        'comments' => 'Comentarios',
    ],
    'bulk' => [
        'form' => [
            'harvest_id' => 'Cosecha',
            'year' => 'Año',
        ],
    ],
    'errors' => [
        'details' => [
            'plant_code_not_found' => 'El código :plant_code no fue encontrado',
        ],
    ],
];
