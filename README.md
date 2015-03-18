# Larablob - Laravel blob store
[![Build Status](https://travis-ci.org/andywer/larablob.svg?branch=master)](https://travis-ci.org/andywer/larablob)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/andywer/larablob/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/andywer/larablob/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/andywer/larablob/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/andywer/larablob/?branch=master)

File uploads made easy. PHP blob store for the famous [Laravel](http://laravel.com/) web framework.


## Why use it?

- You will frequently need to store binary large objects (blobs) like user-uploaded images
- Larablob stores the data separate from your database
- So your database dumps stay small
- Backups are dead easy: Just copy the blob store directory
- Easy to set up and simple to use
- Frequent security pitfalls have been considered and cared for
- Clean high-level API and uncomplicated access on filesystem layer


## Features

- File system based blob storage
- Blobs grouped by named blob groups
- Supports storing blob metadata (stored as JSON files)
- Compatible with Laravel 4.1, 4.2 & 5.0


## Installation

Just run the following command in your project directory:

```bash
composer require andywer/larablob=dev-master      # dev-laravel4 for Laravel 4.1 or 4.2
```

Now add the following line to the `providers` array of your `config/app.php` file:

```php
    'providers' => [
        /* ... */
        'Larablob\LarablobServiceProvider'
    ]
```

And optionally:

```php
    `aliases` => [
        /* ... */
        'BlobStore' => 'Larablob\Facades\BlobStore'
    ]
```


## Laravel 4

If you still use Laravel 4.1 or 4.2, install the package like:

```bash
composer require andywer/larablob=dev-laravel4
```


## Usage

Usage is simple and straight forward. The following sample code shows how to easily store random HTTP POST data and
related metadata into a blob store.

```php
<?php
namespace app\Http\Controllers;

use Larablob\Facades\BlobStore;
use Request;

class PostController extends Controller {

    /** @var \Larablob\Storage\BlobGroup */
    protected $blobGroup;


    public function __construct()
    {
        // the `true` indicates that a new group shall be created if it does not exist
        $this->blobGroup = BlobStore::getBlobGroup('post-data', true);
    }

    /**
     * GET parameters: ['id', 'mime-type']
     * POST data: Random data
     */
    public function postDataUpload()
    {
        $blob = $this->blobGroup->createBlob(Request::input('id'));
        // if we would not pass a blob ID here, the blob store would generate a random UUID v4 for us

        $blob->importFromFile('php://input');
        $blob->setMeta([ 'type' => Request::input('mime-type') ]);

        return response()->json([ 'storedBytes' => $blob->size() ]);
    }

    /**
     * GET parameters: ['id']
     */
    public function retrieveData()
    {
        $blob = $this->blobGroup->getBlob(Request::input('id'));

        return response()->download($blob->getFilePath());
    }

    /**
     * GET parameters: ['id']
     */
    public function retrieveMetadata()
    {
        $blob = $this->blobGroup->getBlob(Request::input('id'));
        $meta = $blob->getMeta();

        return response()->json([ 'type' => $meta->type, 'size' => $blob->size() ]);
    }

    /**
     * Parameters: None
     */
    public function listAll()
    {
        return response()->json([
            'IDs' => $this->blobGroup->allBlobIds()
        ]);
    }

    /**
     * GET parameters: ['id']
     */
    public function removeData()
    {
        $this->blobGroup->getBlob(Request::input('id'))->delete();
        // getBlob() throws a \Larablob\Exceptions\NotFoundException if a bad ID is passed

        return response()->json([ 'success' => true ]);
    }

}
```


## API

### Larablob\Facades\BlobStore

##### BlobStore::getPath()
Returns the path to the blob store base directory on the file system. Defaults to `{project-dir}/storage/larablob`

##### BlobStore::createBlobGroup(string $name)
Creates a new blob group using the supplied `$name` and returns the `BlobGroup` instance. May throw a `Larablob\Exceptions\NamingException` or a `Larablob\Exceptions\AlreadyPresentException`.

##### BlobStore::getBlobGroup(string $name, bool $autoCreate = false)
Returns a `BlobGroup` instance which you can use to create, read, update or delete blobs. If the blob group cannot be found a `Larablob\Exceptions\NotFoundException` is thrown, unless `$autoCreate` is set to true (in this case a new blob group with the given name will be created and returned).

##### BlobStore::allBlobGroups()
Returns an array containing all existing `BlobGroup`s.

##### BlobStore::allBlobGroupNames()
Returns an array containing all existing blob group's names.

##### BlobStore::blobGroupExists(string $name)
Returns `true` if a blob group with this name exists, `false` if not.


### Larablob\Storage\BlobGroup

##### $blobGroup->getName()
Returns the name of the blob group.

##### $blobGroup->getStore()
Returns the `Larablob\Storage\BlobStore` instance of the blob group's store.

##### $blobGroup->createBlob(string $id = null)
Creates a new blob in the blob group and returns a `Blob` instance. You can optionally pass an `$id` to the method (any non-empty string will do; the filename will be `urlencode($id)`) or otherwise Larablob will create a random `UUID v4` for the blob.

Hint: A blob's ID must only be unique in the context of it's blob group.

##### $blobGroup->getBlob(string $id, bool $autoCreate = false)
Returns a `Blob` instance. If the blob cannot be found a `Larablob\Exceptions\NotFoundException` is thrown, unless `$autoCreate` is set to true (in this case a new blob with the given id will be created and returned).

##### $blobGroup->allBlobs()
Returns an array containing all `Blob`s of this blob group.

##### $blobGroup->allBlobIds()
Returns an array containing all blob's identifiers (in this blob group).

##### $blobGroup->blobExists(string $id)
Returns `true` if a blob with the given ID exists, `false` if not.

##### $blobGroup->delete()
Deletes the blob group and all its blobs. Attention: Trying to access the blob group or it's contents after calling `delete()` may result in an exception being thrown.


### Larablob\Storage\Blob

#### $blob->getId()
Returns the blob's identifier as a `string`.

#### $blob->getFilePath()
Returns the path to the blob file as a `string`.

#### $blob->getBlobGroup()
Returns the `BlobGroup` instance of the blob group that contains this blob.

#### $blob->data()
Returns the blob's data as a `string`.

#### $blob->size()
Returns an `integer` indicating the blob data's size in bytes.

#### $blob->save(string $data)
Update the blob's data. Overwrites existing data.

#### $blob->importFromFile(string $filePath)
A shortcut to saving the contents of the given file to the blob. Throws a `Larablob\Exceptions\FileSystemException` if the file cannot be read.

#### $blob->getMeta()
Returns the blob's metadata previously set by `setMeta()` as a generic object (`stdClass`).

#### $blob->setMeta(mixed $metadata)
Saves custom metadata for the blob. The metadata will be encoded to a JSON string and saved to another file.

#### $blob->delete()
Deletes the blob and it's metadata. Attention: Trying to access the blob or it's content after calling `delete()` may result in an exception being thrown.


## Configuration

Currently the only thing to configure is the store path. It defaults to a directory `larablob` in the application's
storage directory.


## License
This software is licensed under the terms of the MIT license. See LICENSE for details.
