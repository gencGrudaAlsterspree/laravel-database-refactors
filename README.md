# Add database refactors to your Laravel app

> This package is an enhanced version of [`signifly/laravel-database-refactors`](https://github.com/signifly/laravel-database-refactors).

The fork allows you to easily add database refactors to your Laravel app (just like the original). This fork tries to imitate the same behaviour as a migration file, using the `up` and `down` methods to refactor or rollback any refactoring made, ultimately synchronising a refactor class with a migration file.

### Why this fork?

The original package does not distinguish between an upward and downward migration (rollbacks) by implementing one single method called `run`. It is also not possible to trace refactors that have run so far, as with migrations using `migrate:status`. Running multiple `artisan db:refactor --class=SomeRefactorClass` could be easily done by mistake.

This fork tries to solve those problems by using a repository to keep track of all refactors that have been run. By adding a `refactor` method in the migration class, the migration class and the refactor class are synchronised without the need of putting a call to the refactor class somewhere in the migrations `up` or `down` method. When synchronised, all refactor classes are controlled with the migration commands.

> This package supports Laravel 6.x and 7.x.

## Installation

You can install the original package via composer:

```bash
composer require signifly/laravel-database-refactors
```

To install this fork and _branch_, use:

```bash
composer require wize-wiz/laravel-database-refactors:"dev-enhancements"
```
Where the repository for this fork is added to the `composer.json`

```bash
    ..
    "repositories": {
        "signifly/laravel-database-refactors": {
            "type": "vcs",
            "url": "wize-wiz/laravel-database-refactors"
        },
    }
    ..
```

## Configuration

Update your `composer.json` file by adding a `database/refactors` directory to your classmap in order to autoload the database refactors:

```
    "autoload": {
        "classmap": [
            "database/seeds",
            "database/factories",
            "database/refactors"
        ],
        "psr-4": {
            "App\\": "app/"
        }
    },
```

If your `classmap` has an older structure as shown below, please update to the latest [`classmap`](https://github.com/laravel/laravel/blob/a70a982cb19e2a623e59964a247562826c487f9e/composer.json#L20) (since Laravel 5.5) structure shown above.

```
    "autoload": {
        "classmap": [
            "database",
        ],
        "psr-4": {
            "App\\": "app/"
        }
    },
```

## Usage

### Repository

You can install the refactor table, which keeps track of all refactors made, by running:

> If a refactor class is called, the table will be created on the fly (if missing) during any migrate or refactor call.

```bash
php artisan refactor:install
```

### Create

Create a refactor class using:

```bash
php artisan make:refactor SomeRefactorClass
```

A new file called `SomeRefactorClass.php` will be available in the `./database/refactors` directory:

```php
<?php

class SomeRefactorClass
{
    public function beforeUp()
    {
        //
    }

    public function up()
    {
        //
    }

    public function beforeDown()
    {
        //
    }

    public function down()
    {
        //
    }
}

```

- The `beforeUp` and `beforeDown` methods are called before the **migration file** executes its `up` or `down` method. These methods are optional and can be removed as such.
- The `up` and `down` methods are executed when the **migration file** finished executing `up` or `down`. These methods are required and an exception will be thrown when missing.

### Compatability

If you wish to remain compatible with the original version of this package, just add an `up` and `down` method and let `up` wrap the original `run` method call, while keeping the `down` method empty.

```php
<?php

class SomeRefactorClass
{
    public function run() 
    {
        // original 
    }
    
    public function up()
    {
        $this->run();
    }
    
    // keep empty
    public function down() {}
}
```

### Link to a migration file

In order to link the refactor class and migration file, we add a `refactor` method in the migration file returning a string with the class name of our refactor class. 

```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SimpleMigration extends Migration
{

    public function up() 
    {
        // migrate up
    }
    
    public function down()
    {
        // migrate down
    }
    
    public function refactor() 
    {
        return 'SomeRefactorClass';
    }

}
```

Whenever the migration file is called, e.g. by any migrate command like `artisan migrate`, `artisan migrate:refresh` or `migrate --path=path/to/migration_file.php`, the refactor class is synchronised with its migration counterpart.

Calling `php artisan migrate` should show the following result:

```bash
Migrating: 2020_07_12_115500_simple_migration
Refactoring: SomeRefactorClass
Refactored: SomeRefactorClass (0.57) seconds)
Migrated:  2020_07_12_115500_simple_migration (0.87 seconds)
```

Calling a rollback with `php artisan migrate:rollback` should show

```bash
Rolling back: 2020_07_12_115500_simple_migration
Refactoring back: SomeRefactorClass
Refactored back: SomeRefactorClass (0.21 seconds)
Rolled back:  2020_07_12_115500_simple_migration (0.55 seconds)
```

You can call a single refactor class in the terminal using:

```bash
php artisan db:refactor --class="UsersTableRefactor"
```

Add the `--rollback` option to rollback any refactors:

```bash
php artisan db:refactor --class="UsersTableRefactor" --rollback
```

To show the status of each refactor, similar to `migrate:status`, run:

```bash
php artisan refactor:status

+------+-------------------+--------------------+-------+
| Ran? | Refactor          | Migration          | Batch |
+------+-------------------+--------------------+-------+
| Yes  | AnotherRefactor   | SomeMigrationTable | 1     |
| Yes  | SomeRefactorClass | -                  | 2     |
| No   | ...Refactor       | -                  |       |
| No   | ...Refactor       | -                  |       |
+------+-------------------+--------------------+-------+
```

Explanation of the table shown above
- refactor `AnotherRefactor` was triggered by migration `SomeMigrationTable`.
- refactor `SomeRefactorClass` was manually triggered with `artisan db:refactor --class=SomeRefactorClass`. 
- all remaining refactors have not been run so far.

## Todo

- `--pretend` support
- snapshots

## Security

If you discover any security issues in the original package [signifly/laravel-database-refactors:master](https://github.com/signifly/laravel-database-refactors), please email dev@signifly.com instead of using the issue tracker.

If you discover any security issues in this fork, throw a PR or use the original package instead.

## Credits

- [Morten Poul Jensen](https://github.com/pactode)
- [All contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
