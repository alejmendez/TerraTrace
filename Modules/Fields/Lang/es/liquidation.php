<?php

return [
    'titles' => [
        'entity_breadcrumb' => 'Liquidaciones',
        'create' => 'Crear Liquidación',
        'edit' => 'Editar Liquidación',
        'show' => 'Detalle Liquidación Semana :week',
    ],
    'table' => [
        'week' => 'Semana',
        'delivery_date' => 'Fecha de entrega y recepción',
        'importer_id' => 'Exportador',
        'total_commercial' => 'KGS Comerciales',
        'total_not_commercial' => 'KGS Rechazo',
    ],
    'weight_unit' => 'Kg',
    'currency_unit' => '$',
    'total' => 'Total',
    'form' => [
        'date' => [
            'label' => 'Semana',
        ],
        'importer_id' => [
            'label' => 'Exportador',
        ],
        'delivery_date' => [
            'label' => 'Fecha de entrega y recepción',
        ],
        'reception_date' => [
            'label' => 'Fecha de exportación',
        ],
        'field_id' => [
            'label' => 'Campo',
        ],
        'weight_with_earth' => [
            'label' => 'Peso con tierra (Kg)',
        ],
        'weight_washed' => [
            'label' => 'Peso lavadas (Kg)',
        ],
        'dollar_value' => [
            'label' => 'Valor del dólar',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'commercial_categories' => [
            'labels' => [
                'name' => 'Categoria',
                'price' => 'Precio comercial (USD/Kg)',
                'weight' => 'Peso (Kg)',
            ],
        ],
    ],
    'sections' => [
        'commercial_categories' => 'Categorías Comerciales',
        'rejected_categories' => 'Categorías Rechazadas',
    ],
];
