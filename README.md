# Hori

Database schema library for Laravel inspired by [Prisma](https://www.prisma.io/).

## How is this different from normal laravel migrations?

In case you've never used the NodeJS library [Prisma](https://www.prisma.io/), what it does is that it handles creating migrations for you.
You have a single schema file where you write all the tables you have and migrations to add/drop tables or columns are automatically generated according to it.

So if you want to remove a column from a table, all you need to do is remove it in the schema file and run `php artisan hori:generate`, which automatically generates a migration that removes that column.

There's 2 advantages to this approach:

1. It's much easier and faster for you to directly work on a single schema file than to manually create migrations for each change
2. You don't need to look at all migrations or the database to know what tables and rows you currently have - you just look at the schema file instead

## Usage

You can install Hori with the command:

```
composer require yui019/hori
```

After that, you run:

```
php artisan hori:install
```

which will create a `hori` directory in the `database` directory with a `schema.php` file inside.

This is what that file looks like by default:

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Yui019\Hori\Schema;

return new class extends Schema
{
    public function create(): void
    {
        $this->table('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $this->createDefaultLaravelTables();

        // ...
    }
};
```

The `$this->createDefaultLaravelTables()` line creates the default laravel tables such as `password_reset_tokens`, `cache`, `jobs`, etc. - i.e. all tables created by the 3 migrations present by default in every laravel project, except for the `users` table.

That table is created in the `schema.php` file right above instead. The reason why I made this choice is that it's very common to want to change something about the default `users` table, whereas all the other ones are left as is 99% of the time, and it felt too cramped to have all those tables in there by default.

This command also deletes those 3 default migrations. You can optionally pass the `--dont-delete-default-migrations` option to avoid that.

---

Next, you run the command:

```
php artisan hori:generate
```

and give it a name for the migration.

This will create a migration in the `database/migrations` directory which will create all those tables.

---

Now, say you want to add a `photo` column to the users table. Normally, you would need to manually create an `add_photo_column` migration which adds the column (and drops it in the `down` method).

With Hori, all you do is add a line such as `$table->string('photo');` to the schema file and run `php artisan hori:generate` again. This will automatically create a migration which does the same thing.

## TODO

Hori supports the following operations:

- creating, modifying and dropping tables
- adding columns, dropping columns and adding foreign key constraints

Renaming tables, modifying columns and renaming columns haven't been implemented yet.

Also, I'm planning to add automatic reordering of tables according to foreign key constraints.
