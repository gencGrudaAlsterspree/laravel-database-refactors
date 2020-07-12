<?php

namespace Signifly\DatabaseRefactors\Listeners;

use Signifly\DatabaseRefactors\Events\BaseEvent;
use Signifly\DatabaseRefactors\Events\RefactorUp;
use Signifly\DatabaseRefactors\Events\RefactorBeforeUp;
use Signifly\DatabaseRefactors\Events\RefactorDown;
use Signifly\DatabaseRefactors\Events\RefactorBeforeDown;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\MigrationEvent;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Exception;
use ReflectionClass;
use Signifly\DatabaseRefactors\Refactorer;
use Signifly\DatabaseRefactors\Repositories\DatabaseRefactorRepository;

class RefactorListener
{

    protected $refactorer;

    public function __construct(Refactorer $refactorer) {
        $this->refactorer = $refactorer;
    }

    /**
     * When a refactor event was called.
     *
     * @param BaseEvent $event
     * @throws \ReflectionException
     * @return void
     */
    public function onRefactor(BaseEvent $event)
    {
        if (! class_exists($event->class)) {
            throw new Exception('Invalid refactor class: '.$event->class);
        }

        $this->refactorer->execute(
            $event->class,
            $event->method,
            get_class($event->migration->migration)
        );
    }

    /**
     * When a migration event was called.
     *
     * @param MigrationEvent $event
     * @return void
     */
    public function onMigration(MigrationEvent $event)
    {
        if(method_exists($event->migration, 'refactor')) {
             $this->fireRefactorEvent($event->migration->refactor(), $event);
        }
    }

    /**
     * Fire event for refactor class.
     *
     * @param string $refactor_class
     * @param MigrationEvent $migration_event
     */
    protected function fireRefactorEvent($refactor_class, $migration_event)
    {
        $before = $migration_event instanceof MigrationStarted;
        $up = $migration_event->method === 'up';
        if($before) {
            $event = $up ?
                RefactorBeforeUp::class : RefactorBeforeDown::class;
        }
        else {
            $event = $up ?
                RefactorUp::class : RefactorDown::class;
        }

        event(new $event($refactor_class, $migration_event));
    }

    /**
     * Subscribe to events.
     *
     * @param Illuminate\Events\Dispatcher $events
     * @return void
     */
    public function subscribe($events)
    {
        // refactor events
        $events->listen(RefactorUp::class, static::class."@onRefactor");
        $events->listen(RefactorBeforeUp::class, static::class."@onRefactor");
        $events->listen(RefactorDown::class, static::class."@onRefactor");
        $events->listen(RefactorBeforeDown::class, static::class."@onRefactor");
        // migration events
        $events->listen(MigrationStarted::class, static::class.'@onMigration');
        $events->listen(MigrationEnded::class, static::class.'@onMigration');
    }

}
