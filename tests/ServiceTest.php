<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 02.03.15
 * Time: 02:49
 */

use Larablob\Facades\BlobStore;

class ServiceTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
        
        App::register('Larablob\LarablobServiceProvider');
    }
    
    public function testIfStoreInitialized()
    {
        $this->assertEquals(storage_path('larablob'), BlobStore::getPath());
    }
    
    public function testProvides()
    {
        $provider = new \Larablob\LarablobServiceProvider(BlobStore::getFacadeApplication());
        
        $this->assertEquals(array('larablob:store'), $provider->provides());
    }
    
}