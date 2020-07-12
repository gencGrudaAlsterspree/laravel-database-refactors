<?php

namespace Signifly\DatabaseRefactors\Events;

use Illuminate\Foundation\Events\Dispatchable;

class BaseEvent
{
    use Dispatchable;

    public $class;
    public $method;

    /**
     * @var Illuminate\Database\Events\MigrationEvent
     */
    public $migration;

    public function __construct($class, $migration = null)
    {
        $this->class = $class;
        $this->migration = $migration;
    }

}
