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
     */
    public function handle()
    {
        $class = $this->option('class');

        if (! class_exists($class)) {
            throw new Exception('Invalid refactor class: '.$class);
        }

        $rollback = $this->option('rollback');
        $method = !$rollback ? 'up' : 'down';
        $before_method = 'before'.ucfirst($method);

        if(method_exists(Refactorer::class, $before_method)) {
            app()->call(Refactorer::class.'@execute', ['class' => $class, 'method' => $before_method]);
        }
        app()->call(Refactorer::class.'@execute', ['class' => $class, 'method' => $method]);
    }
}
