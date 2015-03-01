<?php namespace Larablob;

use Illuminate\Support\ServiceProvider;
use Larablob\Storage\BlobStore;

class LarablobServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->bind('larablob:store', function()
        {
            return new BlobStore(storage_path('larablob'), true);
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('larablob:store');
	}

}
