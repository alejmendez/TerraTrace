<?php

return [
    'titles' => [
        'entity_breadcrumb' => 'Usuarios',
        'create' => 'Crear Usuario',
        'edit' => 'Editar Usuario',
        'show' => 'Detalle del Usuario',
    ],
    'table' => [
        'name' => 'Nombre',
        'dni' => 'RUT / ID',
        'phone' => 'Teléfono',
        'role' => 'Tipo de usuario',
        'email' => 'Correo',
    ],
    'form' => [
        'name' => [
            'label' => 'Nombre',
            'name' => 'nombre',
        ],
        'last_name' => [
            'label' => 'Apellidos',
            'name' => 'apellidos',
        ],
        'email' => [
            'label' => 'Correo electrónico',
            'name' => 'correo electrónico',
        ],
        'password' => [
            'label' => 'Contraseña',
            'name' => 'contraseña',
        ],
        'dni' => [
            'label' => 'RUT / ID',
            'name' => 'rut',
        ],
        'avatar' => [
            'label' => 'Seleccione un imagen',
            'name' => 'seleccione un imagen',
        ],
        'phone' => [
            'label' => 'Teléfono',
            'name' => 'teléfono',
        ],
        'role' => [
            'label' => 'Tipo de usuario',
            'name' => 'tipo de usuario',
        ],
    ],
    'show' => [
        'created_at' => 'Fecha de creación',
        'updated_at' => 'Última modificación',
        'file' => [
            'title' => 'Detalles del perfil',
            'name' => [
                'label' => 'Nombre',
            ],
            'last_name' => [
                'label' => 'Apellidos',
            ],
            'email' => [
                'label' => 'Correo electrónico',
            ],
            'password' => [
                'label' => 'Contraseña',
            ],
            'dni' => [
                'label' => 'RUT / ID',
            ],
            'avatar' => [
                'label' => 'Seleccione un imagen',
            ],
            'phone' => [
                'label' => 'Teléfono',
            ],
            'role' => [
                'label' => 'Tipo de usuario',
            ],
        ],
        'activity' => [
            'title' => 'Actividad',
        ],
        'statistics' => [
            'title' => 'Estadísticas',
        ],
        'tabs' => [
            'file' => 'Ficha',
            'activity' => 'Actividad',
            'statistics' => 'Estadísticas',
        ],
    ],
    'sections' => [
        'details' => 'Detalles del perfil',
        'login' => 'Inicio de sesión',
        'roles' => 'Permisos',
    ],
    'buttons' => [
        'change_email' => 'Cambiar correo',
        'resend_password' => 'Reenvía la contraseña',
    ],
];
