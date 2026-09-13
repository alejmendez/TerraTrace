<?php

namespace Modules\Core\Http\Controllers;

use Inertia\Inertia;

class ForbiddenController extends Controller
{
    public function forbidden()
    {
        return Inertia::render('Core::Forbidden');
    }
}
