<?php

namespace Cooperl\Database\DB2;

use Cooperl\Database\DB2\Connectors\ODBCConnector;
use Cooperl\Database\DB2\Connectors\IBMConnector;
use Cooperl\Database\DB2\Connectors\ODBCZOSConnector;
use Illuminate\Support\ServiceProvider;
use Config;

/**
 * Class DB2ServiceProvider
 *
 * @package Cooperl\Database\DB2
 */
class DB2ServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // get the configs
        $conns = is_array(Config::get('laravel-db2::database.connections'))
            ? Config::get('laravel-db2::database.connections')
            : [];

        // Add my database configurations to the default set of configurations
        $this->app['config']['database.connections'] = array_merge(
            $conns,
            $this->app['config']['database.connections']
        );

        // Extend the connections with pdo_odbc and pdo_ibm drivers
        foreach (Config::get('database.connections') as $conn => $config) {
            // Only use configurations that feature a "odbc", "ibm" or "odbczos" driver
            if (!isset($config['driver']) || !in_array($config['driver'], ['odbc', 'ibm', 'odbczos'])) {
                continue;
            }

            // Create a connector
            $this->app['db']->extend($conn, function ($config) {
                switch ($config['driver']) {
                    case 'odbc':
                        $connector = new ODBCConnector();

                        break;
                    case 'odbczos':
                        $connector = new ODBCZOSConnector();

                        break;
                    case 'ibm':
                    default:
                        $connector = new IBMConnector();

                        break;
                }

                $db2Connection = $connector->connect($config);

                return new DB2Connection($db2Connection, 'ehmdb', '', $config);
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
