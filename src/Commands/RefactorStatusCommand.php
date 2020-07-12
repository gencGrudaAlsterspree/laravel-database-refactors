<?php

namespace Signifly\DatabaseRefactors\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Signifly\DatabaseRefactors\Repositories\DatabaseRefactorRepository;

class RefactorStatusCommand extends Command {

    protected $signature = 'refactor:status';

    protected $description = 'Show the status of each refactor';

    protected $repository;

    public function __construct(DatabaseRefactorRepository $repository) {
        parent::__construct();
        $this->repository = $repository;
    }

    public function handle() {
        $run = $this->repository->getRun();
        $classes = $this->repository->getRefactorClasses();

//        var_dump($this->getStatusFor($run, $classes));
        if (count($refactors = $this->getStatusFor($run, $classes)) > 0) {
            $this->table(['Ran?', 'Refactor', 'Migration', 'Batch'], $refactors);
        } else {
            $this->error('No refactors found');
        }
    }

    protected function getStatusFor($run, $classes) {
        $batches = $run->map(function($batch) {
            return [
                'refactor' => $batch->refactor,
                'migration' => $batch->migration,
                'batch' => $batch->batch
            ];
        })->keyBy('refactor');
        $run_classes = $run->pluck('migration', 'refactor');

        return Collection::make($classes)->map(function($file, $class) use ($run_classes, $batches) {

            $batch = $batches->get($class);
            $number = $batch['batch'];
            $migration = $batch['migration'];

            return $run_classes->has($class) ?
                ['<info>Yes</info>', $class, $migration ? $migration : '-', $number] :
                ['<fg=red>No</fg=red>', $class, $migration ? $migration : '-', $number];

        });
    }

}
