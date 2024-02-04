# Laravel Repositories generator
 
[![Latest Stable Version](https://poser.pugx.org/lab2view/laravel-repository-generator/v/stable)](https://packagist.org/packages/lab2view/laravel-repository-generator)
[![Total Downloads](https://poser.pugx.org/lab2view/laravel-repository-generator/downloads)](https://packagist.org/packages/lab2view/laravel-repository-generator)
[![Monthly Downloads](https://poser.pugx.org/lab2view/laravel-repository-generator/d/monthly)](https://packagist.org/packages/lab2view/laravel-repository-generator)
[![License](https://poser.pugx.org/lab2view/laravel-repository-generator/license)](https://packagist.org/packages/lab2view/laravel-repository-generator)

Laravel Repositories Generator is a package for Laravel used to generate repositories from Eloquent models.

## Installation

Run the following command from your terminal:

 ```bash
 composer require "lab2view/laravel-repository-generator"
 ```

## Usage

Generate repository classes from Eloquent models in the Models folder:

 ```bash
 php artisan make:repositories
 ```

Use the generated repository in the controller:

```php
<?php namespace App\Http\Controllers;

use App\Repositories\PostRepository;

class PostController extends Controller {

    private $postRepository;
    
    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index() {
        return response()->json($this->postRepository->getAll());
    }
}
```


## Available Methods

The following methods are available:

##### Lab2view\RepositoryGenerator\RepositoryInterface

```php
    public function exists(string $key, $value, $withTrashed = false)

    public function getByAttribute(string $attr_name, $attr_value, $relations = [], $withTrashed = false, $selects = [])

    public function getPaginate(int $n, $relations = [], $withTrashed = false, $selects = []);

    public function store(Array $inputs)

    public function getById($id, $relations = [], $withTrashed = false, $selects = [])

    public function search($key, $value, $relations = [], $withTrashed = false, $selects = [])

    public function getAll($relations = [], $withTrashed = false, $selects = [])

    public function countAll($withTrashed = false)

    public function getAllSelectable($key)

    public function update($id, Array $inputs)

    public function destroy($id)

    public function destroyAll()

    public function forceDelete($id)

    public function restore($id)
```

## Example usage

Create a new post in repository:

```php
$post = $this->postRepository->store($request->all());
```

Update an existing post:

```php
$post = $this->postRepository->update($post_id, $request->all());
```

Delete post:

```php
$post = $this->postRepository->destroy($id);
```

Get a post by post_id:

```php
$post = $this->postRepository->getById($id);
```

you can also choose what relations to eager load:

```php
$post = $this->postRepository->getById($id, ['comments']);
```

## Contributing

Thank you for considering contributing to this Laravel package.


## Credits

This package is inspired by [this](https://github.com/bosnadev/repository) great package by @bosnadev.

