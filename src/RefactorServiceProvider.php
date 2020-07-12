<?php

namespace Signifly\DatabaseRefactors;

use Illuminate\Support\ServiceProvider;
use Signifly\DatabaseRefactors\Commands\RefactorDbCommand;
use Signifly\DatabaseRefactors\Commands\RefactorInstallCommand;
use Signifly\DatabaseRefactors\Commands\RefactorMakeCommand;
use Illuminate\Support\Facades\Event;
use Signifly\DatabaseRefactors\Commands\RefactorResetCommand;
use Signifly\DatabaseRefactors\Repositories\DatabaseRefactorRepository;
use Signifly\DatabaseRefactors\Repositories\RefactorRepositoryInterface;

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
                RefactorDbCommand::class,
                RefactorMakeCommand::class,
                RefactorInstallCommand::class,
                RefactorResetCommand::class
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
        $this->app->bind(RefactorRepositoryInterface::class, DatabaseRefactorRepository::class);
        Event::subscribe(Listeners\RefactorListener::class);
    }
}
