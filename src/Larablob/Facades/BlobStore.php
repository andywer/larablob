<?php
/**
 * Created by PhpStorm.
 * User: andy
 * Date: 01.03.15
 * Time: 18:41
 */

namespace Larablob\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * Class BlobStore
 * @package Larablob\Facades
 * 
 * @method static \Larablob\Storage\BlobGroup createBlobGroup(string $name)
 * @method static \Larablob\Storage\BlobGroup getBlobGroup(string $name, bool $autoCreate = false)
 * @method static string[] allBlobGroupNames()
 * @method static bool blobGroupExists(string $name)
 */
class BlobStore extends Facade {

    /** @return string */
    protected static function getFacadeAccessor() { return 'larablob:store'; }
    
}