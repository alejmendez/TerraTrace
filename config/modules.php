<?php

use Modules\Auth\Providers\AuthServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Dashboard\Providers\DashboardServiceProvider;
use Modules\Fields\Providers\FieldsServiceProvider;
use Modules\Tasks\Providers\TasksServiceProvider;
use Modules\Users\Providers\UsersServiceProvider;

return [
    'providers' => [
        CoreServiceProvider::class,
        UsersServiceProvider::class,
        AuthServiceProvider::class,
        DashboardServiceProvider::class,
        FieldsServiceProvider::class,
        TasksServiceProvider::class,
    ],
];
