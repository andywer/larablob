# larablob
[![Build Status](https://travis-ci.org/andywer/larablob.svg?branch=master)](https://travis-ci.org/andywer/larablob)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/andywer/larablob/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/andywer/larablob/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/andywer/larablob/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/andywer/larablob/?branch=master)

Laravel local blob storage


## Installation

Just run the following command in your project directory:

```bash
composer require andywer/larablob=dev-master      # dev-laravel4 if you are using Laravel 4.1 or 4.2
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


## Features

- File system based blob storage
- Grouped blobs
- Supports storing blob metadata (stored as JSON files)
- Compatible with Laravel 4.1, 4.2 & 5.0


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


## Configuration

Currently the only thing to configure is the store path. It defaults to a directory `larablob` in the application's
storage directory.


## Laravel 4

If you still use Laravel 4.1 or 4.2, install the package like:

```bash
composer require andywer/larablob=dev-laravel4
```


## License
This software is licensed under the terms of the MIT license. See LICENSE for details.
