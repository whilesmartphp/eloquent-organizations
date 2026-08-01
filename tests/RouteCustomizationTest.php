<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase as Orchestra;
use Whilesmart\Organizations\Contracts\ResponseFormatter;
use Whilesmart\Organizations\Http\Controllers\OrganizationController;
use Whilesmart\Organizations\Http\Resources\OrganizationResource;
use Whilesmart\Organizations\Models\Organization;
use Whilesmart\Organizations\OrganizationsServiceProvider;

class CustomOrganizationController extends OrganizationController
{
    public function index(Request $request, ?string $workspaceId = null): JsonResponse
    {
        $organization = new Organization;
        $organization->forceFill(['name' => 'Example']);
        $resource = config('organizations.resource');

        return $this->success(new $resource($organization));
    }
}

class CustomOrganizationResource extends OrganizationResource
{
    public function toArray($request): array
    {
        return [...parent::toArray($request), 'custom_resource' => true];
    }
}

class CustomOrganizationResponseFormatter implements ResponseFormatter
{
    public function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'custom_formatter' => true,
        ], $statusCode);
    }

    public function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'custom_formatter' => true,
        ], $statusCode);
    }
}

class RouteCustomizationTest extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [OrganizationsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('organizations.controller', CustomOrganizationController::class);
        $app['config']->set('organizations.route_actions', ['index', 'store']);
        $app['config']->set('organizations.route_write_middleware', ['organization-write']);
        $app['config']->set('organizations.register_workspace_routes', false);
        $app['config']->set('organizations.register_member_routes', false);
        $app['config']->set('organizations.route_middleware', []);
        $app['config']->set('organizations.resource', CustomOrganizationResource::class);
        $app['config']->set('organizations.response_formatter', CustomOrganizationResponseFormatter::class);
    }

    public function test_it_configures_the_controller_routes_and_write_middleware(): void
    {
        $routes = $this->app['router']->getRoutes();
        $index = $routes->getByName('organizations.index');
        $store = $routes->getByName('organizations.store');

        $this->assertInstanceOf(Route::class, $index);
        $this->assertInstanceOf(Route::class, $store);
        $this->assertSame(CustomOrganizationController::class.'@index', $index->getActionName());
        $this->assertContains('organization-write', $store->gatherMiddleware());
        $this->assertNull($routes->getByName('organizations.show'));

        $uris = collect($routes)->map(fn (Route $route) => $route->uri())->all();

        $this->assertNotContains('workspaces/{workspaceId}/organizations', $uris);
        $this->assertNotContains('organizations/{id}/members', $uris);
    }

    public function test_it_uses_the_configured_resource_and_response_formatter(): void
    {
        $this->getJson('/organizations')
            ->assertOk()
            ->assertJsonPath('data.name', 'Example')
            ->assertJsonPath('data.custom_resource', true)
            ->assertJsonPath('custom_formatter', true);
    }
}
