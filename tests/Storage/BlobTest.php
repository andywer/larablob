<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 22:38
 */

namespace Larablob\Storage;

use \Mockery as m;
use File;

class BlobTest extends \TestCase {

    /** @var Blob */
    protected $blob;
    
    /** @var \Mockery\MockInterface|BlobGroup */
    protected $blobGroup;
    
    /** @var string */
    protected $blobId = 'this_is/../the.test-id!';
    
    /** @var string */
    protected $blobFilePath;
    
    
    public function setUp()
    {
        parent::setUp();
        
        $this->blobGroup = m::mock('Larablob\Storage\BlobGroup');
        
        $this->blobFilePath = $this->tempDirectory.'/'.urlencode($this->blobId);
        $this->blob = new Blob($this->blobGroup, $this->blobId, $this->blobFilePath);
        
        File::put($this->blobFilePath, '');
    }
    
    public function testGetters()
    {
        $blob = $this->blob;
        
        $this->assertEquals($this->blobId, $blob->getId());
        $this->assertEquals($this->blobFilePath, $blob->getFilePath());
        $this->assertEquals($this->blobGroup, $blob->getBlobGroup());
    }
    
    public function testSavingAndRetrievingData()
    {
        $blob = $this->blob;
        
        $testData = str_random(1024*1024);        // 1 MB random data
        $blob->save($testData);
        $this->assertBlobStoresData($blob, $testData);
        
        $testData2 = 'Foo bar';
        $blob->save($testData2);
        $this->assertBlobStoresData($blob, $testData2);
        
        $testData3 = 'Test file content...';
        $tempFilePath = $this->tempDirectory.'/temp-file';
        
        File::put($tempFilePath, $testData3);
        $blob->importFromFile($tempFilePath);
        $this->assertBlobStoresData($blob, $testData3);
    }
    
    public function testMetaData()
    {
        $blob = $this->blob;
        
        $meta = (object)array( 'foo' => 'bar', 'nested' => (object)array( 'items' => array(), 'count' => 0 ) );
        $blob->setMeta($meta);
        
        $this->assertEquals(json_encode($meta), File::get($this->blobFilePath.' meta.json'));
        $this->assertEquals($meta, $blob->getMeta());
        
        $meta2 = (object)array( 'type' => 'image/jpeg' );
        $blob->setMeta($meta2);

        $this->assertEquals(json_encode($meta2), File::get($this->blobFilePath.' meta.json'));
        $this->assertEquals($meta2, $blob->getMeta());
    }
    
    public function testDelete()
    {
        $this->blob->delete();
        
        $this->assertNotTrue(File::exists($this->blobFilePath));
    }


    /**
     * @param Blob $blob
     * @param string $data
     */
    protected function assertBlobStoresData(Blob $blob, $data)
    {
        $this->assertEquals($data, File::get($this->blobFilePath));
        $this->assertEquals($data, $blob->data());
        $this->assertEquals(strlen($data), $blob->size());
    }

}