<?php

/*
|--------------------------------------------------------------------------
| config/database.php — Production-ready for Render + Supabase PostgreSQL
|--------------------------------------------------------------------------
|
| KEY RULES APPLIED HERE:
|
| 1. NO fallback values for credentials. If an env var is missing, the
|    connection must FAIL LOUD rather than silently use 'forge'/'127.0.0.1'.
|    Use `env('VAR')` with no second argument — returns null, which forces
|    a clear error rather than a silent wrong-credential connection.
|
| 2. DB_CONNECTION is explicitly set to 'pgsql' as the default. This
|    single env var controls which connection block is used.
|
| 3. The Supabase connection uses port 5432 (direct) or 6543 (pooler).
|    Use 6543 + sslmode=require for the Supabase connection pooler.
|    Use 5432 + sslmode=require for direct connections (needed for migrations).
|
| 4. SSL is enforced. Supabase rejects unencrypted connections.
|
*/

use Illuminate\Support\Str;

return [

    /*
    |----------------------------------------------------------------------
    | Default Database Connection
    |----------------------------------------------------------------------
    |
    | Set DB_CONNECTION=pgsql in your Render environment variables.
    | This MUST match the key name in the 'connections' array below.
    |
    */
    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |----------------------------------------------------------------------
    | Database Connections
    |----------------------------------------------------------------------
    */
    'connections' => [

        'sqlite' => [
            'driver'                  => 'sqlite',
            'url'                     => env('DATABASE_URL'),
            'database'                => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix'                  => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        /*
        |------------------------------------------------------------------
        | PostgreSQL — Supabase (Primary connection used in production)
        |------------------------------------------------------------------
        |
        | Render environment variables to set:
        |
        |   DB_CONNECTION = pgsql
        |   DB_HOST       = db.<your-project-ref>.supabase.co
        |   DB_PORT       = 5432          (direct) or 6543 (pooler)
        |   DB_DATABASE   = postgres
        |   DB_USERNAME   = postgres.<your-project-ref>  (for pooler)
        |                   or postgres                   (for direct)
        |   DB_PASSWORD   = <your-supabase-db-password>
        |   DB_SSLMODE    = require
        |
        | For migrations, always use direct port 5432.
        | PgBouncer (port 6543) does not support prepared statements
        | which Laravel's query builder relies on.
        |
        */
        'pgsql' => [
            'driver'         => 'pgsql',

            /*
             * DATABASE_URL takes precedence if set (some Render templates
             * auto-inject it). If not set, individual vars are used.
             * Remove 'url' if you want individual vars ONLY.
             */
            'url'            => env('DATABASE_URL'),

            /*
             * No fallback values — must fail loud if env vars are missing.
             * Never use 'forge', '127.0.0.1', 'laravel', or 'secret' here.
             */
            'host'           => env('DB_HOST'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE'),
            'username'       => env('DB_USERNAME'),
            'password'       => env('DB_PASSWORD'),

            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'public',
            'sslmode'        => env('DB_SSLMODE', 'require'),

            /*
             * Supabase requires SSL. These options are passed directly
             * to the underlying PDO driver.
             */
            'options' => extension_loaded('pdo_pgsql') ? array_filter([
                PDO::ATTR_EMULATE_PREPARES => true,  // Required for PgBouncer (port 6543)
                PDO::ATTR_TIMEOUT          => 10,
            ]) : [],
        ],

        /*
        |------------------------------------------------------------------
        | MySQL (kept for completeness — not used in this project)
        |------------------------------------------------------------------
        */
        'mysql' => [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'laravel'),
            'username'       => env('DB_USERNAME', 'root'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
            'options'        => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        /*
        |------------------------------------------------------------------
        | SQL Server (kept for completeness — not used)
        |------------------------------------------------------------------
        */
        'sqlsrv' => [
            'driver'         => 'sqlsrv',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', 'localhost'),
            'port'           => env('DB_PORT', '1433'),
            'database'       => env('DB_DATABASE', 'laravel'),
            'username'       => env('DB_USERNAME', 'root'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |----------------------------------------------------------------------
    | Migration Repository Table
    |----------------------------------------------------------------------
    */
    'migrations' => [
        'table'  => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |----------------------------------------------------------------------
    | Redis
    |----------------------------------------------------------------------
    */
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster'    => env('REDIS_CLUSTER', 'redis'),
            'prefix'     => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

    ],

];