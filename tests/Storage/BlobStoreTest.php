<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 02.03.15
 * Time: 02:05
 */

namespace Larablob\Storage;

use File;

class BlobStoreTest extends \TestCase {
    
    /** @var BlobStore */
    protected $blobStore;
    
    /** @var string */
    protected $blobStorePath;
    
    /** @var BlobGroup[] */
    protected $blobGroups;
    

    public function setUp()
    {
        parent::setUp();
        
        $this->blobStorePath = $this->tempDirectory.'/store';
        
        File::makeDirectory($this->blobStorePath);
        $this->blobStore = new BlobStore($this->blobStorePath);
        
        $this->createSampleGroups();
    }

    /**
     * @expectedException \Larablob\Exceptions\FileSystemException
     */
    public function testStoreDirectoryNotFound()
    {
        new BlobStore('/this/path/does/not/exist');
    }
    
    public function testStoreAutoCreate()
    {
        $storePath = $this->tempDirectory.'/auto-created-store';
        new BlobStore($storePath, true);
        
        $this->assertTrue(File::isDirectory($storePath));
        File::deleteDirectory($storePath);
    }

    public function testGetters()
    {
        $this->assertEquals($this->blobStorePath, $this->blobStore->getPath());
    }

    public function testBlobGroupCreation()
    {
        $group1 = $this->blobGroups[0];
        $group2 = $this->blobGroups[1];
        
        $group1Path = $this->getBlobGroupPath($group1);
        $group2Path = $this->getBlobGroupPath($group2);
        
        $this->assertEquals('group1', $group1->getName());
        $this->assertTrue(File::isDirectory($group1Path));
        $this->assertEquals(array(), File::files($group1Path));
        $this->assertEquals($this->blobStore, $group1->getStore());

        $this->assertEquals('another group', $group2->getName());
        $this->assertTrue(File::isDirectory($group2Path));
        $this->assertEquals(array(), File::files($group2Path));
        $this->assertEquals($this->blobStore, $group2->getStore());
    }

    /**
     * @expectedException \Larablob\Exceptions\NamingException
     */
    public function testIllegalGroupName()
    {
        $this->blobStore->createBlobGroup('');
    }

    /**
     * @expectedException \Larablob\Exceptions\AlreadyPresentException
     */
    public function testDuplicateGroupCreation()
    {
        $this->blobStore->createBlobGroup('group1');
    }
    
    public function testGetBlobGroup()
    {
        $group = $this->blobStore->getBlobGroup('group1');
        
        $this->assertInstanceOf('Larablob\Storage\BlobGroup', $group);
        $this->assertEquals('group1', $group->getName());
    }

    /**
     * @expectedException \Larablob\Exceptions\NotFoundException
     */
    public function testBlobGroupNotFound()
    {
        $this->blobStore->getBlobGroup('does not exist');
    }
    
    public function testGetBlobGroupAutoCreate()
    {
        $group = $this->blobStore->getBlobGroup('auto-created', true);
        $groupPath = $this->getBlobGroupPath($group);
        
        $this->assertEquals('auto-created', $group->getName());
        $this->assertTrue(File::isDirectory($groupPath));
        
        $this->blobGroups[] = $group;
    }
    
    public function testAllBlobGroupNames()
    {
        $actualBlobGroupNames = $this->blobStore->allBlobGroupNames();
        $expectedBlobGroupNames = array_map(function(BlobGroup $blobGroup)
        {
            return $blobGroup->getName();
        }, $this->blobGroups);
        
        sort($actualBlobGroupNames);
        sort($expectedBlobGroupNames);
        
        $this->assertEquals($expectedBlobGroupNames, $actualBlobGroupNames);
    }
    
    public function testBlobGroupExists()
    {
        $store = $this->blobStore;
        
        foreach ($this->blobGroups as $blobGroup) {
            $this->assertTrue($store->blobGroupExists($blobGroup->getName()));
        }
        
        $this->assertNotTrue($store->blobGroupExists('does not exist'));
    }
    
    
    protected function createSampleGroups()
    {
        $store = $this->blobStore;
        
        $this->blobGroups = array(
            $store->createBlobGroup('group1'),
            $store->createBlobGroup('another group')
        );
    }

    /**
     * @param BlobGroup $group
     * @return string
     */
    protected function getBlobGroupPath(BlobGroup $group)
    {
        return $this->blobStorePath.'/'.urlencode($group->getName());
    }
    
}