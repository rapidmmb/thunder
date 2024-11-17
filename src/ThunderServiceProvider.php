<?php

use Illuminate\Support\ServiceProvider;

class ThunderServiceProvider extends ServiceProvider
{

    protected array $commands = [
        Commands\ThunderStartCommand::class,
    ];

    public function register()
    {
        $this->registerConfig();
        $this->commands($this->commands);
    }

    public function registerConfig()
    {
        $config = __DIR__ . '/../config/thunder.php';

        $this->publishes([$config => base_path('config/thunder.php')], ['thunder']);

        $this->mergeConfigFrom($config, 'thunder');
    }

}