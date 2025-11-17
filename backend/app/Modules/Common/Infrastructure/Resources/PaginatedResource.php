<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

final class PaginatedResource extends ResourceCollection
{
    public function __construct($resource, protected string $resourceClass)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return [
            'data' => $this->resourceClass::collection($this->collection),
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
            'meta' => [
                'currentPage' => $this->currentPage(),
                'from' => $this->firstItem(),
                'lastPage' => $this->lastPage(),
                'path' => $this->path(),
                'perPage' => $this->perPage(),
                'to' => $this->lastItem(),
                'total' => $this->total(),
            ],
        ];
    }
}
