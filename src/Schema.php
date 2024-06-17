<?php

namespace Yui019\Hori;

use Closure;
use Illuminate\Database\Schema\Blueprint;

class Schema
{
    public array $blueprints = [];

    /**
     * Create a new table.
     */
    public function table(string $name, Closure $callback): void
    {
        $blueprint = new Blueprint($name);
        $callback($blueprint);

        array_push($this->blueprints, $blueprint);
    }

    /**
     * Create default laravel tables.
     * These are the tables that are created by the 3 migrations that are
     * present by default in every laravel project.
     * This function does not, however, create the users table, because it is
     * very common to want to modify it. Instead that table is moved to the
     * default hori schema template.
     */
    public function createDefaultLaravelTables(): void
    {
        /// create_users_table
        /// ==================

        // $this->table('users', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('email')->unique();
        //     $table->timestamp('email_verified_at')->nullable();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps();
        // });

        $this->table('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        $this->table('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        /// create_cache_table
        /// ==================

        $this->table('cache', function (blueprint $table) {
            $table->string('key')->primary();
            $table->mediumtext('value');
            $table->integer('expiration');
        });

        $this->table('cache_locks', function (blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        /// create_jobs_table
        /// ==================

        $this->table('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        $this->table('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        $this->table('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }
}
