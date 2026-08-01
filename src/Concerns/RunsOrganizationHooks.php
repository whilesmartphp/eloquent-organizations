<?php

namespace Whilesmart\Organizations\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Whilesmart\Organizations\Contracts\OrganizationHook;
use Whilesmart\Organizations\Enums\OrganizationAction;

trait RunsOrganizationHooks
{
    protected function runBeforeHooks(Request $request, OrganizationAction $action): Request
    {
        foreach ($this->organizationHooks() as $hook) {
            $request = $hook->before($request, $action);
        }

        return $request;
    }

    protected function runAfterHooks(
        Request $request,
        JsonResponse $response,
        OrganizationAction $action,
    ): JsonResponse {
        foreach ($this->organizationHooks() as $hook) {
            $response = $hook->after($request, $response, $action);
        }

        return $response;
    }

    /** @return list<OrganizationHook> */
    private function organizationHooks(): array
    {
        return array_map(function (string $hookClass): OrganizationHook {
            $hook = app()->make($hookClass);

            if (! $hook instanceof OrganizationHook) {
                throw new InvalidArgumentException(
                    'Configured organization hooks must implement '.OrganizationHook::class.'.',
                );
            }

            return $hook;
        }, config('organizations.hooks', []));
    }
}
