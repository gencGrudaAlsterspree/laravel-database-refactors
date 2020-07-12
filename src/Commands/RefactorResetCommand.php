<?php

namespace Signifly\DatabaseRefactors\Commands;

use Illuminate\Console\Command;
use Signifly\DatabaseRefactors\Repositories\RefactorRepositoryInterface;

class RefactorResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refactor:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the refactor repository';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(RefactorRepositoryInterface $repository)
    {
        if(!$repository->repositoryExists()) {
            return $this->info('Refactor table does not exist, nothing to reset.');
        }

        $repository->reset();
        $this->info('Refactor table reset successful.');
    }

}
