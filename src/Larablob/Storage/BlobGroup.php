<?php namespace Larablob\Storage;
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 18:03
 */

use File;
use Larablob\Exceptions\AlreadyPresentException;
use Larablob\Exceptions\NamingException;
use Larablob\Exceptions\NotFoundException;
use Rhumsaa\Uuid\Uuid;

class BlobGroup {
    
    /** @var BlobStore */
    protected $store;

    /** @var string */
    protected $name;

    /** @var string */
    protected $path;
    
    /** @var Blob[] */
    protected $instantiatedBlobs = array();


    /**
     * @param BlobStore $blobStore
     * @param string $name
     * @param string $path
     */
    public function __construct(BlobStore $blobStore, $name, $path)
    {
        $this->store = $blobStore;
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * @return BlobStore
     */
    public function getStore() { return $this->store; }

    /**
     * @return void
     */
    public function delete()
    {
        File::deleteDirectory($this->path);
    }

    /**
     * @param string [$id]
     * @return Blob
     * @throws NamingException
     * @throws AlreadyPresentException
     */
    public function createBlob($id = null)
    {
        if ($id === null) { $id = Uuid::uuid4()->toString(); }
        
        if (!is_string($id) || strlen($id) < 1) { throw new NamingException('Illegal blob id: '.$id); }
        if ($this->blobExists($id)) { throw new AlreadyPresentException('Blob exists: '.$id); }

        $blobPath = $this->getBlobPath($id);
        File::put($blobPath, '');

        $blob = $this->blobInstance($id, $blobPath);
        $blob->setMeta(new \stdClass());
        
        return $blob;
    }

    /**
     * @param string $id
     * @param bool $autoCreate
     * @return Blob
     * @throws AlreadyPresentException
     * @throws NotFoundException
     */
    public function getBlob($id, $autoCreate = false)
    {
        if (!$this->blobExists($id)) {
            if (!$autoCreate) { throw new NotFoundException('Blob not found: '.$id); }

            return $this->createBlob($id);
        }

        return $this->blobInstance($id, $this->getBlobPath($id));
    }

    /**
     * @return Blob[]
     */
    public function allBlobs()
    {
        $blobs = array();

        foreach ($this->allBlobIds() as $blobId) {
            $blobs[] = $this->getBlob($blobId);
        }

        return $blobs;
    }

    /**
     * @return string[]
     */
    public function allBlobIds()
    {
        $blobFiles = File::files($this->path);
        $blobIds = array();

        foreach ($blobFiles as $blobFileName) {
            if (substr($blobFileName, -10) === ' meta.json') { continue; }
            
            $blobIds[] = $this->unescapeBlobId(basename($blobFileName));
        }

        return $blobIds;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function blobExists($id)
    {
        return File::exists($this->getBlobPath($id));
    }


    /**
     * @param string $id
     * @param string $blobPath
     * @return Blob
     */
    protected function blobInstance($id, $blobPath)
    {
        if (!isset($this->instantiatedBlobs[$id])) {
            $this->instantiatedBlobs[$id] = new Blob($this, $id, $blobPath);
        }
        
        return $this->instantiatedBlobs[$id];
    }


    /**
     * @param string $id
     * @return string
     */
    protected function getBlobPath($id)
    {
        $escapedId = $this->escapeBlobId($id);

        return $this->path.'/'.$escapedId;
    }

    /**
     * @param string $id
     * @return string
     */
    protected function escapeBlobId($id)
    {
        return urlencode($id);
    }

    /**
     * @param string $pathName
     * @return string
     */
    protected function unescapeBlobId($pathName)
    {
        return urldecode($pathName);
    }

}