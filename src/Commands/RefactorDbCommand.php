<?php

namespace Signifly\DatabaseRefactors\Commands;

use Exception;
use Illuminate\Console\Command;
use Signifly\DatabaseRefactors\Refactorer;

class RefactorDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:refactor {--class=} {--rollback}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refactor database by specified refactor class';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @todo: pretty chaotic with compatibility check `run`, we'll remove run and only use `up` and `down` as required methods.
     */
    public function handle()
    {
        $class = $this->option('class');

        if (! class_exists($class)) {
            throw new Exception('Invalid refactor class: '.$class);
        }

        $rollback = $this->option('rollback');
        // @todo: use up instead of run
        $method = !$rollback ? 'run' : 'down';
        app()->call(Refactorer::class.'@execute', ['class' => $class, 'method' => $method]);
    }
}
