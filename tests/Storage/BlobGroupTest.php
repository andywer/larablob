<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 23:24
 */

namespace Larablob\Storage;

use \Mockery as m;
use File;

class BlobGroupTest extends \TestCase {
    
    /** @var BlobGroup */
    protected $blobGroup;
    
    /** @var BlobStore */
    protected $blobStore;
    
    /** @var string */
    protected $blobGroupName = 'Name of_this.blob%Group';
    
    /** @var string */
    protected $blobGroupPath;
    
    /** @var Blob[] */
    protected $blobs = array();
    
    
    public function setUp()
    {
        parent::setUp();
        
        $this->blobStore = m::mock('Larablob\Storage\BlobStore');

        $this->blobGroupPath = $this->tempDirectory.'/'.urlencode($this->blobGroupName);
        $this->blobGroup = new BlobGroup($this->blobStore, $this->blobGroupName, $this->blobGroupPath);
        
        File::makeDirectory($this->blobGroupPath);
        
        $this->createSampleBlobs();
    }
    
    public function tearDown()
    {
        $this->delete();
        
        parent::tearDown();
    }
    
    
    public function testGetters()
    {
        $blobGroup = $this->blobGroup;
        
        $this->assertEquals($this->blobGroupName, $blobGroup->getName());
        $this->assertEquals($this->blobStore, $blobGroup->getStore());
    }
    
    public function testBlobCreation()
    {
        $blob1 = $this->blobs[0];
        $blob2 = $this->blobs[1];
        $blob3 = $this->blobs[2];
        
        $blob1FilePath = $this->blobGroupPath.'/'.urlencode($blob1->getId());
        
        $this->assertEquals('given blob id', $blob1->getId());
        $this->assertTrue(File::exists($blob1FilePath));
        $this->assertEquals(0, File::size($blob1FilePath));
        
        $blob2FilePath = $this->blobGroupPath.'/'.urlencode($blob2->getId());
        
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $blob2->getId());
        $this->assertTrue(File::exists($blob2FilePath));
        $this->assertEquals(0, File::size($blob2FilePath));

        $blob3FilePath = $this->blobGroupPath.'/'.urlencode($blob3->getId());

        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $blob3->getId());
        $this->assertNotEquals($blob2->getId(), $blob3->getId());
        $this->assertTrue(File::exists($blob3FilePath));
        $this->assertEquals(0, File::size($blob3FilePath));
    }

    public function testBlobExists()
    {
        $blobGroup = $this->blobGroup;
        
        foreach ($this->blobs as $blob) {
            $this->assertTrue($blobGroup->blobExists($blob->getId()), 'blobExists() returned false for "'.$blob->getId().'"');
        }
        
        $this->assertNotTrue($blobGroup->blobExists('another blob id'));
    }

    /**
     * @expectedException \Larablob\Exceptions\NamingException
     */
    public function testIllegalBlobName()
    {
        $this->blobGroup->createBlob('');
    }

    /**
     * @expectedException \Larablob\Exceptions\AlreadyPresentException
     */
    public function testDuplicateBlobCreation()
    {
        $this->blobGroup->createBlob($this->blobs[0]->getId());
    }
    
    public function testGetBlob()
    {
        $blobGroup = $this->blobGroup;
        $blob1 = $this->blobs[0];
        
        $blob = $blobGroup->getBlob($blob1->getId());
        
        $this->assertInstanceOf('Larablob\Storage\Blob', $blob);
        $this->assertEquals($blob1->getId(), $blob->getId());
        $this->assertEquals($blobGroup, $blob->getBlobGroup());
    }
    
    public function testGetAllBlobIds()
    {
        $actualBlobIds = $this->blobGroup->allBlobIds();
        $expectedBlobIds = array_map(function(Blob $blob)
        {
            return $blob->getId();
        }, $this->blobs);
        
        sort($actualBlobIds);
        sort($expectedBlobIds);
        
        $this->assertEquals($expectedBlobIds, $actualBlobIds);
    }
    
    
    /**
     * @return Blob[]
     */
    protected function createSampleBlobs()
    {
        $blobGroup = $this->blobGroup;

        $this->blobs = array(
            $blobGroup->createBlob('given blob id'),
            $blobGroup->createBlob(),
            $blobGroup->createBlob()
        );
    }

    protected function delete()
    {
        $this->blobGroup->delete();

        $this->assertNotTrue(File::exists($this->blobGroupPath));
    }

}