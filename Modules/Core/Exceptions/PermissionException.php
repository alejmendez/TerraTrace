<?php

namespace Modules\Core\Exceptions;

use Exception;

class PermissionException extends Exception
{
    protected $routeName;

    public function __construct($routeName)
    {
        $this->routeName = $routeName;
    }

    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthorized',
                'routeName' => $this->routeName,
            ], 403);
        }

        return redirect()->route('forbidden');
    }
}
