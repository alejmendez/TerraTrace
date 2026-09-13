<?php

return [
    'providers' => [
        Modules\Core\Providers\CoreServiceProvider::class,
        Modules\Users\Providers\UsersServiceProvider::class,
        Modules\Auth\Providers\AuthServiceProvider::class,
        Modules\Dashboard\Providers\DashboardServiceProvider::class,
        Modules\Fields\Providers\FieldsServiceProvider::class,
        Modules\Tasks\Providers\TasksServiceProvider::class,
    ],
];
