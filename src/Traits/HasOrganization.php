<?php

namespace Whilesmart\Organizations\Traits;

use Whilesmart\Organizations\Models\Organization;

trait HasOrganization
{
    public function organizations()
    {
        return $this->morphMany(Organization::class, 'owner');

    }
}
