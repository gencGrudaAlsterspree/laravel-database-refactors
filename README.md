# Add database refactors to your Laravel app

> This package is an enhanced version of [`signifly/laravel-database-refactors`](https://github.com/signifly/laravel-database-refactors).

The fork allows you to easily add database refactors to your Laravel app (just like the original). This fork tries to imitate the same behaviour as a migration file, using the `up` and `down` methods to refactor or rollback any refactoring made.

> This package supports Laravel 6.x and 7.x.

## Installation

You can install the original package via composer:

```bash
composer require signifly/laravel-database-refactors
```

To install this fork and _branch_, use:

```bash
composer require wize-wiz/laravel-database-refactors:"dev-enhancments"
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

If your `classmap` has an older structure like:

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

Then please update to the latest `classmap` structure shown above.

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
        // 
    }
    
    public function down()
    {
        //
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

## Security

If you discover any security issues in the original package [signifly/laravel-database-refactors:master](https://github.com/signifly/laravel-database-refactors), please email dev@signifly.com instead of using the issue tracker.

## Credits

- [Morten Poul Jensen](https://github.com/pactode)
- [All contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
