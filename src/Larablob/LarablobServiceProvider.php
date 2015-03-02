<?php namespace Larablob;

use Config;
use Larablob\Storage\BlobStore;

class LarablobServiceProvider extends \Illuminate\Support\ServiceProvider {

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
        $this->mergeConfigFrom(
            __DIR__.'/../../config/larablob.php', 'larablob'
        );
    }
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
	{
        $this->app->bind('larablob:store', function()
        {
            return new BlobStore(Config::get('larablob.store_path'), true);
        });
	}

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return ['larablob:store'];
    }

}
