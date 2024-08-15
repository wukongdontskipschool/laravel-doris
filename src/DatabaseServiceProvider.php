<?php

namespace Wukongdontskipschool\LaravelDoris;

use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('db.connector.doris', Database\Connectors\DorisConnector::class);

        \Illuminate\Database\Connection::resolverFor('doris', function ($connection, $database, $prefix, $config) {
            return new Database\DorisConnection($connection, $database, $prefix, $config);
        });
    }
}
