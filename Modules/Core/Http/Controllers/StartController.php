<?php

namespace Modules\Core\Http\Controllers;

use Inertia\Inertia;

class StartController extends Controller
{
    public function index()
    {
        return Inertia::render('Core::Start');
    }
}
