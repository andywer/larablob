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
use Larablob\Exceptions\NotFoundException;

class BlobStore {
    
    /** @var string */
    protected $path;


    /**
     * @param string $path
     * @throws FileSystemException
     */
    public function __construct($path)
    {
        if (!File::isDirectory($path)) { throw new FileSystemException('Not a directory: '.$path); }
        
        $this->path = $path;
    }

    /**
     * @param string $name
     * @return BlobGroup
     * @throws AlreadyPresentException
     */
    public function createBlobGroup($name)
    {
        if ($this->blobGroupExists($name)) { throw new AlreadyPresentException('Blob group exists: '.$name); }
        
        $blobGroupPath = $this->getBlobGroupPath($name);
        
        File::makeDirectory($blobGroupPath);
        return new BlobGroup($this, $name, $blobGroupPath);
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
        
        return new BlobGroup($this, $name, $this->getBlobGroupPath($name));
    }

    /**
     * @return string[]
     */
    public function allBlobGroups()
    {
        $directoryNames = File::directories($this->path);
        $blobGroupNames = array();
        
        foreach ($directoryNames as $dirName) {
            $blobGroupNames[] = $this->unescapeBlobGroupName($dirName);
        }
        
        return $blobGroupNames;
    }

    public function blobGroupExists($name)
    {
        return File::exists($this->getBlobGroupPath($name));
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