<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 22:34
 */

use Mockery as m;

abstract class TestCase extends Orchestra\Testbench\TestCase {

    /** @var string */
    protected $tempDirectory = '/tmp/larablob-test';
    
    
    public function setUp()
    {
        parent::setUp();
        
        if (File::exists($this->tempDirectory)) { File::deleteDirectory($this->tempDirectory); }
        File::makeDirectory($this->tempDirectory);
    }
    
    public function tearDown()
    {
        m::close();

        parent::tearDown();
    }
    
}