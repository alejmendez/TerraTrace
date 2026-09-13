<?php

return [
    'titles' => [
        'entity_breadcrumb' => 'Cuarteles',
        'create' => 'Crear Cuartel',
        'edit' => 'Editar Cuartel',
        'show' => 'Detalle del Cuartel: :name',
    ],
    'table' => [
        'name' => 'Cuartel',
        'field_id' => 'Campo',
        'planned_at' => 'F. plantación',
        'area' => 'Superficie',
        'plants_count' => 'Arboles',
    ],
    'form' => [
        'name' => [
            'label' => 'Nombre del cuartel',
        ],
        'location' => [
            'label' => 'Ubicación',
        ],
        'area' => [
            'label' => 'Superficie del cuartel (Hectáreas)',
        ],
        'planned_at' => [
            'label' => 'Fecha de plantación',
        ],
        'plants_count' => [
            'label' => 'Cantidad de arboles',
        ],
        'field_id' => [
            'label' => 'Perteneciente al campo',
        ],
        'responsible_id' => [
            'label' => 'Responsable',
        ],
        'blueprint' => [
            'label' => 'Seleccione sus planos',
        ],
    ],
    'show' => [
        'file' => [
            'field' => 'Perteneciente al campo',
            'area' => 'Superficie del cuartel (hectáreas)',
            'plants_count' => 'Cantidad de arboles',
            'responsible' => 'Responsable',
        ],
        'harvest' => [
            'title' => 'Cosecha',
        ],
        'logbook' => [
            'title' => 'Bitácora',
        ],
        'statistics' => [
            'title' => 'Estadísticas',
            'scale_type' => [
                'label' => 'Tipo de escala',
                'options' => [
                    'weight' => 'Peso',
                    'quantity' => 'Cantidad',
                ],
            ],
        ],
        'tabs' => [
            'file' => 'Ficha',
            'harvest' => 'Cosecha',
            'logbook' => 'Bitácora',
            'statistics' => 'Estadísticas',
        ],
    ],
    'sections' => [
        'blueprint' => 'Planos',
    ],
];
