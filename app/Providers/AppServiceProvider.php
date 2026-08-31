<?php

namespace App\Providers;

use App\Interfaces\BreadcrumbInterfaces;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->composeBreadcrumbs();
    }

    /**
     * Feeds the breadcrumb partial from the current controller's
     * getBreadcrumbs(), so pages never have to declare their own trail.
     * Controllers that don't implement the contract fall back to the
     * page title, which the partial handles.
     */
    private function composeBreadcrumbs(): void
    {
        View::composer('layouts.admin.header.breadcrumbs-main', function ($view) {
            $controller = request()->route()?->controller;

            $breadcrumbs = $controller instanceof BreadcrumbInterfaces
                ? $controller->getBreadcrumbs()
                : [];

            $view->with('breadcrumbs', $breadcrumbs);
        });
    }
}
