<?php

namespace Signifly\DatabaseRefactors\Commands;

use Exception;
use Illuminate\Console\Command;
use ReflectionClass;
use Signifly\DatabaseRefactors\Repositories\DatabaseRefactorRepository;

class RefactorInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refactor:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the refactor repository';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $repository = app(DatabaseRefactorRepository::class);
        if($repository->repositoryExists()) {
            return $this->info('Refactor table already exists.');
        }

        $repository->createRepository();
        $this->info('Refactor table created successfully.');
    }

}
