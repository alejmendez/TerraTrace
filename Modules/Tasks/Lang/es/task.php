<?php

return [
    'titles' => [
        'entity_breadcrumb' => 'Tareas',
        'create' => 'Crear Tarea',
        'edit' => 'Editar Tarea',
        'show' => 'Detalle de la Tarea',
    ],
    'table' => [
        'correlative' => 'Correlativo',
        'name' => 'Tarea',
        'priority' => 'Prioridad',
        'status' => 'Estado',
        'updated_at' => 'Actualización',
        'end_date' => 'Fecha fin',
        'responsible' => 'Responsable',
    ],
    'form' => [
        'name' => [
            'label' => 'Tarea',
        ],
        'repeat_number' => [
            'label' => 'Repetir',
        ],
        'repeat_type' => [
            'options' => [
                'daily' => 'Diario',
                'weekly' => 'Semanalmente',
                'monthly' => 'Mensualmente',
            ],
        ],
        'priority' => [
            'label' => 'Prioridad',
            'options' => [
                'when_possible' => 'Cuando se pueda',
                'routine' => 'Rutinario',
                'important' => 'Importante',
                'urgent' => 'Urgente',
            ],
        ],
        'status' => [
            'label' => 'Estado',
            'options' => [
                'to_begin' => 'Por iniciar',
                'started' => 'Iniciada',
                'stopped' => 'Detenida',
                'finished' => 'Terminada',
                'overdued' => 'Atrasada',
            ],
        ],
        'start_date' => [
            'label' => 'Fecha inicio',
        ],
        'end_date' => [
            'label' => 'Fecha fin',
        ],
        'field_id' => [
            'label' => 'Campo',
        ],
        'quarter_id' => [
            'label' => 'Cuartel(es)',
        ],
        'plant_id' => [
            'label' => 'Planta(s)',
        ],
        'rows' => [
            'label' => 'Hilera(s)',
        ],
        'responsible_id' => [
            'label' => 'Responsable',
        ],
        'tools' => [
            'label' => 'Herramientas',
        ],
        'security_equipments' => [
            'label' => 'Elementos de seguridad',
        ],
        'machineries' => [
            'label' => 'Maquinarias',
        ],
        'supplies' => [
            'name' => [
                'label' => 'Insumo',
            ],
            'brand' => [
                'label' => 'Marca',
            ],
            'quantity' => [
                'label' => 'Cantidad',
            ],
            'unit' => [
                'label' => 'Unidad',
                'options' => [
                    'unit' => 'Unidad (ud)',
                    'package' => 'Paquete (paq)',
                    'box' => 'Caja (caja)',
                    'pallet' => 'Pallet (pal)',
                    'container' => 'Contenedor (cont)',
                    'grams' => 'Gramos (g)',
                    'kilograms' => 'Kilogramos (kg)',
                    'tons' => 'Toneladas (ton)',
                    'milliliters' => 'Mililitros (ml)',
                    'liters' => 'Litros (l)',
                    'gallons' => 'Galones (gal)',
                    'barrels' => 'Barriles (bbl)',
                    'millimeters' => 'Milímetros (mm)',
                    'centimeters' => 'Centímetros (cm)',
                    'meters' => 'Metros (m)',
                    'kilometers' => 'Kilómetros (km)',
                    'inches' => 'Pulgadas (in)',
                    'feet' => 'Pies (ft)',
                    'yards' => 'Yardas (yd)',
                ],
            ],
        ],
        'comments' => [
            'entity' => 'comentario',
            'placeholder' => 'Ingrese comentarios adicionales',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
            'new_comment' => 'Agregar nuevo comentario',
        ],
    ],
    'show' => [
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Última modificación',
        'detail' => [
            'title' => 'Detalle',
            'priority' => [
                'label' => 'Prioridad',
                'options' => [
                    'when_possible' => 'Cuando se pueda',
                    'routine' => 'Rutinario',
                    'important' => 'Importante',
                    'urgent' => 'Urgente',
                ],
            ],
            'status' => [
                'label' => 'Estado',
                'options' => [
                    'to_begin' => 'Por iniciar',
                    'started' => 'Iniciada',
                    'stopped' => 'Detenida',
                    'finished' => 'Terminada',
                    'overdued' => 'Atrasada',
                ],
            ],
            'start_date' => [
                'label' => 'Fecha inicio',
            ],
            'end_date' => [
                'label' => 'Fecha fin',
            ],
            'responsible' => [
                'label' => 'Responsable',
            ],
            'tools' => [
                'label' => 'Herramientas',
            ],
            'security_equipments' => [
                'label' => 'Elementos de seguridad',
            ],
            'machineries' => [
                'label' => 'Maquinarias',
            ],
            'supplies' => [
                'name' => [
                    'label' => 'Insumo',
                ],
                'brand' => [
                    'label' => 'Marca',
                ],
                'quantity' => [
                    'label' => 'Cantidad',
                ],
                'unit' => [
                    'label' => 'Unidad',
                    'options' => [
                        'unit' => 'Unidad (ud)',
                        'package' => 'Paquete (paq)',
                        'box' => 'Caja (caja)',
                        'pallet' => 'Pallet (pal)',
                        'container' => 'Contenedor (cont)',
                        'grams' => 'Gramos (g)',
                        'kilograms' => 'Kilogramos (kg)',
                        'tons' => 'Toneladas (ton)',
                        'milliliters' => 'Mililitros (ml)',
                        'liters' => 'Litros (l)',
                        'gallons' => 'Galones (gal)',
                        'barrels' => 'Barriles (bbl)',
                        'millimeters' => 'Milímetros (mm)',
                        'centimeters' => 'Centímetros (cm)',
                        'meters' => 'Metros (m)',
                        'kilometers' => 'Kilómetros (km)',
                        'inches' => 'Pulgadas (in)',
                        'feet' => 'Pies (ft)',
                        'yards' => 'Yardas (yd)',
                    ],
                ],
            ],
            'sections' => [
                'resources' => 'Recursos',
                'comments' => 'Comentarios',
            ],
        ],
        'tracking' => [
            'title' => 'Seguimiento',
        ],
        'logbook' => [
            'title' => 'Bitácora',
        ],
        'statistics' => [
            'title' => 'Estadísticas',
        ],
        'tabs' => [
            'detail' => 'Detalle',
            'tracking' => 'Seguimiento',
            'logbook' => 'Bitácora',
            'statistics' => 'Estadísticas',
        ],
    ],
    'sections' => [
        'assignment' => 'Asignación',
        'resources' => 'Recursos',
        'comments' => 'Comentarios',
    ],
    'buttons' => [
        'add_supply' => 'Añadir otro',
    ],
];
