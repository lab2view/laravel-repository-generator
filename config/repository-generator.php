<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Directories
    |--------------------------------------------------------------------------
    |
    | The default directory structure
    |
    */

    'repository_directory' => app_path('Repositories/'),
    'model_directory' => app_path('Models/'),

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | The namespace of repository and models
    |
    */
    'model_namespace' => 'App\Models',
    'repository_namespace' => 'App\Repositories',

    /*
    |--------------------------------------------------------------------------
    | Main Repository File
    |--------------------------------------------------------------------------
    |
    | The main repository class, other repositories will be extended from this
    |
    | If you're working with your customized repository file
    | You should change these values like below,
    |
    | 'base_repository_file' => 'CustomFile.php'
    | 'base_repository_class' => 'App\Custom\Repository:class'
    */

    // Only file name of the file because full path can cause errors.
    // We're gonna use "repository_directory" config value for it.
    'base_repository_file' => 'BaseRepository.php',
    // Class name as string
    'base_repository_class' => \Lab2View\RepositoryGenerator\BaseRepository::class,
];
