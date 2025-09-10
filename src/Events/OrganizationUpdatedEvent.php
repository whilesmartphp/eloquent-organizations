<?php

namespace Whilesmart\Organizations\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Whilesmart\Organizations\Models\Organization;

class OrganizationUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Organization $organization;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }
}
