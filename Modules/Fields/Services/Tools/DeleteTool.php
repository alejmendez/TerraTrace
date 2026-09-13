<?php

namespace Modules\Fields\Services\Tools;

use Modules\Fields\Models\Tool;

class DeleteTool
{
    public static function call($id): void
    {
        Tool::destroy($id);
    }
}
