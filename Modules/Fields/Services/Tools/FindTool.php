<?php

namespace Modules\Fields\Services\Tools;

use Modules\Fields\Models\Tool;

class FindTool
{
    public static function call($id)
    {
        $tool = Tool::findOrFail($id);

        return $tool;
    }
}
