<?php

namespace Signifly\DatabaseRefactors;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class RefactorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\RefactorDbCommand::class,
                Commands\RefactorMakeCommand::class,
                Commands\RefactorInstallCommand::class,
                Commands\RefactorResetCommand::class,
                Commands\RefactorStatusCommand::class
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Repositories\RefactorRepositoryInterface::class, Repositories\DatabaseRefactorRepository::class);
        Event::subscribe(Listeners\RefactorListener::class);
    }
}
