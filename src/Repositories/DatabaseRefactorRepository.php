<?php

namespace Signifly\DatabaseRefactors\Repositories;

use Illuminate\Database\ConnectionResolverInterface as Resolver;
use Symfony\Component\Console\Output\ConsoleOutput;
use Throwable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class DatabaseRefactorRepository implements RefactorRepositoryInterface {

    protected $exists;
    protected $path;
    protected $files;
    protected $resolver;
    protected $table;
    protected $connection;

    public function __construct(Resolver $resolver, Filesystem $files) {
        $this->path = database_path('refactors');
        $this->files = $files;
        $this->resolver = $resolver;
        $this->table = 'refactors';
    }

    /**
     * Create the refactor repository data store.
     *
     * @return void
     */
    public function createRepository()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            $table->increments('id');
            $table->string('refactor');
            $table->string('migration')->nullable();
            $table->integer('batch');
            $table->unique(['refactor', 'migration']);
        });

        $this->exists = true;
    }

    /**
     * Determine if the refactor repository exists.
     *
     * @return bool
     */
    public function repositoryExists()
    {
        if($this->exists === null) {
            $schema = $this->getConnection()->getSchemaBuilder();
            $this->exists = $schema->hasTable($this->table);
        }
        return $this->exists;
    }

    /**
     * Get the last refactor batch number.
     *
     * @return int
     */
    public function getLastBatchNumber()
    {
        return (integer) $this->table()->max('batch');
    }

    /**
     * Log that a refactor has run.
     *
     * @param  string  $class
     * @param  int  $batch
     * @return void
     */
    public function log($class, $migration = null, $batch = null)
    {
        try {
            $record = ['refactor' => $class, 'migration' => $migration, 'batch' => $batch ?? ($this->getLastBatchNumber() + 1)];

            $this->table()->insert($record);
        } catch(Throwable $e) {}
    }

    /**
     * Remove a refactor from the log.
     *
     * @param  object  $class
     * @return void
     */
    public function delete($class)
    {
        return $this->table()->where('refactor', $class)->delete();
    }

    /**
     * If a refactor has run before.
     *
     * @param $class
     * @return bool
     */
    public function hasRun($class)
    {
        return $this->table()->where('refactor', $class)->count() > 0;
    }

    /**
     * Get the completed refactors.
     *
     * @return mixed
     */
    public function getRun()
    {
        return $this->table()
            ->orderBy('refactor', 'asc')
            ->orderBy('batch', 'asc')
            ->get();
    }

    /**
     * Get all available refactor classes.
     *
     * @param $path
     * @return mixed
     */
    public function getRefactorClasses() {
        return Collection::make($this->files->glob($this->path.'/*.php'))
            ->map(function($file) {
                $name = explode(DIRECTORY_SEPARATOR, $file);
                $name = $name[count($name)-1];
                return ['class' => str_replace('.php', '', $name), 'file' => $file];
            })
            ->keyBy('class')
            ->all();
    }

    /**
     * Reset all refactors.
     *
     * @return void
     */
    public function reset()
    {
        return $this->table()->delete();
    }

    /**
     * Get a query builder for the refactor table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table()
    {
        $this->check();
        return $this->getConnection()->table($this->table)->useWritePdo();
    }

    /**
     * Check if the repository exists, if not, create.
     */
    protected function check()
    {
        if(!$this->exists && !$this->repositoryExists()) {
            $this->createRepository();
        }
    }

    /**
     * Resolve the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->resolver->connection($this->connection);
    }

    /**
     * Add console output when running migration commands.
     *
     * @param $class
     * @param $method
     * @param null $time
     */
    public function setConsoleOutput($class, $method, $time = null)
    {
        $time = number_format($time/60, 2);
        $line = "";
        $show_seconds = false;
        switch($method) {
            case 'skipped':
                $line .= '<comment>Refactoring skipped:</comment>';
                break;
            case 'beforeUp':
                $line .= '<info>Refactoring:</info>';
                break;
            case 'beforeDown':
                $line .= '<info>Refactoring back:</info>';
                break;
            case 'run': // @deprecated
            case 'up':
                $line .= '<comment>Refactored:</comment>';
                $show_seconds = true;
                break;
            case 'down':
                $line .= '<comment>Refactored back:</comment>';
                $show_seconds = true;
                break;
        }
        $this->getConsole()->writeln($show_seconds ? "{$line} {$class} ({$time} seconds)" : "{$line} {$class}");
    }

    /**
     * Return a console instance.
     *
     * @return ConsoleOutput
     */
    protected function getConsole()
    {
        return new ConsoleOutput();
    }

}
