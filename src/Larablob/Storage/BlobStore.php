<?php namespace Larablob\Storage;
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 17:10
 */

use File;
use Larablob\Exceptions\AlreadyPresentException;
use Larablob\Exceptions\FileSystemException;
use Larablob\Exceptions\NamingException;
use Larablob\Exceptions\NotFoundException;

class BlobStore {
    
    /** @var string */
    protected $path;

    /** @var BlobGroup[] */
    protected $instantiatedBlobGroups = array();


    /**
     * @param string $path
     * @param bool $autoCreate
     * @throws FileSystemException
     */
    public function __construct($path, $autoCreate = false)
    {
        if (!File::isDirectory($path)) {
            if (!$autoCreate) { throw new FileSystemException('Not a directory: '.$path); }
            
            File::makeDirectory($path);
        }
        
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath() { return $this->path; }

    /**
     * @param string $name
     * @return BlobGroup
     * @throws NamingException
     * @throws AlreadyPresentException
     */
    public function createBlobGroup($name)
    {
        if (!is_string($name) || strlen($name) < 1) { throw new NamingException('Illegal blob group name: '.$name); }
        
        if ($this->blobGroupExists($name)) { throw new AlreadyPresentException('Blob group exists: '.$name); }
        
        $blobGroupPath = $this->getBlobGroupPath($name);
        File::makeDirectory($blobGroupPath);

        return $this->blobGroupInstance($name, $blobGroupPath);
    }

    /**
     * @param string $name
     * @param bool [$autoCreate]
     * @return BlobGroup
     * @throws NotFoundException
     */
    public function getBlobGroup($name, $autoCreate = false)
    {
        if (!$this->blobGroupExists($name)) {
            if (!$autoCreate) { throw new NotFoundException('Blob group not found: '.$name); }
            
            return $this->createBlobGroup($name);
        }
        
        return $this->blobGroupInstance($name, $this->getBlobGroupPath($name));
    }

    /**
     * @return BlobGroup[]
     */
    public function allBlobGroups()
    {
        $blobGroups = array();
        
        foreach ($this->allBlobGroupNames() as $groupName) {
            $blobGroups[] = $this->getBlobGroup($groupName);
        }
        
        return $blobGroups;
    }

    /**
     * @return string[]
     */
    public function allBlobGroupNames()
    {
        $directoryNames = File::directories($this->path);
        $blobGroupNames = [];
        
        foreach ($directoryNames as $dirName) {
            $blobGroupNames[] = $this->unescapeBlobGroupName(basename($dirName));
        }
        
        return $blobGroupNames;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function blobGroupExists($name)
    {
        return File::exists($this->getBlobGroupPath($name));
    }


    /**
     * @param string $name
     * @param string $groupPath
     * @return BlobGroup
     */
    protected function blobGroupInstance($name, $groupPath)
    {
        if (!isset($this->instantiatedBlobGroups[$name])) {
            $this->instantiatedBlobGroups[$name] = new BlobGroup($this, $name, $groupPath);
        }
        
        return $this->instantiatedBlobGroups[$name];
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getBlobGroupPath($name)
    {
        $escapedName = $this->escapeBlobGroupName($name);
        
        return $this->path.'/'.$escapedName;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function escapeBlobGroupName($name)
    {
        return urlencode($name);
    }

    /**
     * @param string $pathName
     * @return string
     */
    protected function unescapeBlobGroupName($pathName)
    {
        return urldecode($pathName);
    }

}