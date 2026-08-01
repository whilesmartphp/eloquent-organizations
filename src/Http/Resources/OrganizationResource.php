<?php

namespace Whilesmart\Organizations\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
