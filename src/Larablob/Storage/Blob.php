<?php namespace Larablob\Storage;
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 18:08
 */

use File;
use Larablob\Exceptions\FileSystemException;

class Blob {
    
    /** @var BlobGroup */
    protected $blobGroup;
    
    /** @var string */
    protected $id;
    
    /** @var string */
    protected $path;
    
    /** @var string */
    protected $metaFilePath;


    /**
     * @param BlobGroup $group
     * @param string $id
     * @param string $path
     */
    public function __construct(BlobGroup $group, $id, $path)
    {
        $this->blobGroup = $group;
        $this->id = $id;
        $this->path = $path;
        $this->metaFilePath = $path.' meta.json';
    }

    /**
     * @return string
     */
    public function getId() { return $this->id; }

    /**
     * @return string
     */
    public function getFilePath() { return $this->path; }

    /**
     * @return BlobGroup
     */
    public function getBlobGroup() { return $this->blobGroup; }

    /**
     * @return void
     */
    public function delete()
    {
        File::delete($this->path);
        
        if (File::exists($this->metaFilePath)) { File::delete($this->metaFilePath); }
    }

    /**
     * @return string
     */
    public function data()
    {
        return File::get($this->path);
    }

    /**
     * @return int
     */
    public function size()
    {
        return File::size($this->path);
    }

    /**
     * @param string $data
     */
    public function save($data)
    {
        File::put($this->path, $data);
        clearstatcache();                   // File::size() tends to return obsolete values if clearstatcache() is not called
    }

    /**
     * @param string $filePath
     * @throws FileSystemException
     */
    public function importFromFile($filePath)
    {
        $result = File::copy($filePath, $this->path);

        if (!$result) { throw new FileSystemException('Cannot import from: '.$filePath); }
        
        clearstatcache();                   // File::size() tends to return obsolete values if clearstatcache() is not called
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        $unparsed = File::get($this->metaFilePath);
        
        return json_decode($unparsed);
    }

    /**
     * @param mixed $metaData
     */
    public function setMeta($metaData)
    {
        $parsed = json_encode($metaData);
        
        File::put($this->metaFilePath, $parsed);
    }

}