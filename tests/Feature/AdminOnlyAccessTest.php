<?php

namespace Tests\Feature;

use App\Components\Datatables\Datatables;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Who may set the company up.
 *
 * Since the portal opened to ordinary staff, "administrator only" stopped being
 * a property of the app and became a property of each route. The likely way to
 * break it is not to edit the middleware but to add a route in the wrong place
 * - a new team or KPI screen registered outside the admin group reads as
 * perfectly ordinary code and is open to everyone. So the assertion is made
 * over the route table itself rather than by logging a user in and poking one
 * page, and no database is needed to make it.
 */
class AdminOnlyAccessTest extends TestCase
{
    /**
     * Every route whose name starts with one of these configures the company:
     * its teams, positions, members, projects, tags, KPIs and weighting.
     */
    private const ADMIN_PREFIXES = [
        'positions.',
        'teams.',
        'staff.',
        'projects.',
        'project-tags.',
        'kpi.',
        'kpi-categories.',
        'kpi-settings.',
        'manage-pending.',
        'project-tasks.',
    ];

    /**
     * The exceptions, each deliberate: a staff member moves their own work
     * along and reads what is attached to it. Both check ownership in
     * ProjectTaskController::authorizeTask().
     */
    private const OPEN_TO_STAFF = [
        'project-tasks.status',
        'project-tasks.attachments',
    ];

    public function test_every_administrative_route_is_behind_the_admin_middleware(): void
    {
        $unguarded = $this->routes()
            ->filter(fn (RoutingRoute $route) => $this->isAdministrative($route->getName()))
            ->reject(fn (RoutingRoute $route) => in_array('admin', $route->gatherMiddleware(), true))
            ->map(fn (RoutingRoute $route) => $route->getName())
            ->values()
            ->all();

        $this->assertSame([], $unguarded,
            'These routes set the company up but any signed-in member can reach them: '.implode(', ', $unguarded));
    }

    public function test_the_pages_a_staff_member_needs_are_not_behind_it(): void
    {
        $expected = ['dashboard', 'my.kpi', 'my.tasks', 'password.change', 'datatables.listing', 'logout'];

        foreach (array_merge($expected, self::OPEN_TO_STAFF) as $name) {
            $route = $this->routes()->first(fn (RoutingRoute $r) => $r->getName() === $name);

            $this->assertNotNull($route, "Route [{$name}] is missing.");
            $this->assertNotContains('admin', $route->gatherMiddleware(),
                "Route [{$name}] is administrator-only, but staff need it.");
        }
    }

    /**
     * Everything behind the portal door sits behind the door: it is easy to
     * hang a new route off the file's bottom, outside every group.
     */
    public function test_nothing_but_the_login_screens_is_reachable_signed_out(): void
    {
        $public = ['login', 'login.attempt', 'password.request', 'password.email', 'password.reset', 'password.update'];

        $open = $this->routes()
            ->filter(fn (RoutingRoute $route) => $route->getName() !== null)
            ->reject(fn (RoutingRoute $route) => in_array($route->getName(), $public, true))
            // Registered by packages, not by this app's route file.
            ->reject(fn (RoutingRoute $route) => str_starts_with($route->getName(), 'ignition.')
                || str_starts_with($route->getName(), 'sanctum.'))
            ->reject(fn (RoutingRoute $route) => in_array('auth', $route->gatherMiddleware(), true))
            ->map(fn (RoutingRoute $route) => $route->getName())
            ->values()
            ->all();

        $this->assertSame([], $open, 'Reachable without signing in: '.implode(', ', $open));
    }

    /**
     * The list endpoint takes its class from the request, so the routes alone
     * do not protect the rows - see DatatablesController.
     */
    public function test_lists_are_administrator_only_unless_they_say_otherwise(): void
    {
        $open = collect(config('datatables.classes'))
            ->reject(fn (string $class) => $this->listClass($class)::adminOnly())
            ->values()
            ->all();

        $this->assertSame(['ProjectTaskList'], $open,
            'Only the task list is scoped to its viewer; any other open list hands its rows to everyone.');
    }

    public function test_a_list_is_administrator_only_by_default(): void
    {
        $this->assertTrue(Datatables::adminOnly(),
            'A new list must be closed until it is deliberately opened, not the other way round.');
    }

    private function isAdministrative(?string $name): bool
    {
        if ($name === null || in_array($name, self::OPEN_TO_STAFF, true)) {
            return false;
        }

        foreach (self::ADMIN_PREFIXES as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /** @return \Illuminate\Support\Collection<int, RoutingRoute> */
    private function routes(): \Illuminate\Support\Collection
    {
        return collect(Route::getRoutes()->getRoutes());
    }

    /** @return class-string<Datatables> */
    private function listClass(string $class): string
    {
        return "\\App\\Components\\Datatables\\{$class}";
    }
}
