<?php

namespace Modules\Fields\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HarvestListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'current_page' => $this->getCurrentPage(),
            'first_page_url' => $this->firstPageUrl,
            'from' => $this->from,
            'last_page' => $this->lastPage,
            'last_page_url' => $this->lastPageUrl,
            'next_page_url' => $this->nextPageUrl,
            'path' => $this->path,
            'per_page' => $this->perPage,
            'prev_page_url' => $this->prevPageUrl,
            'to' => $this->to,
            'total' => $this->total,
            'links' => $this->links,
        ];
    }
}
