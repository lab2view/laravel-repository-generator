<?php

return [

    'user_class' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Directories
    |--------------------------------------------------------------------------
    |
    | The default directory structure
    |
    */

    'models_directory' => app_path('Models/'),
    'contracts_directory' => app_path('Contracts/'),
    'repositories_directory' => app_path('Repositories/'),
    'policies_directory' => app_path('Policies/'),

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | The namespace of repository and models
    |
    */
    'models_namespace' => 'App\Models',
    'contracts_namespace' => 'App\Contracts',
    'repositories_namespace' => 'App\Repositories',
    'policies_namespace' => 'App\Policies',

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
    // We're going to use "repository_directory" config value for it.
    'base_repository_file' => 'BaseRepository.php',
    // Class name as string
    'base_repository_class' => \Lab2view\RepositoryGenerator\BaseRepository::class,

    // We're going to use "contracts_directory" config value for it.
    'base_contract_file' => 'RepositoryInterface.php',
    // Interface name as string
    'base_contract_interface' => \Lab2view\RepositoryGenerator\RepositoryInterface::class,

    // Base class name as string
    'base_policy_class' => \Lab2view\RepositoryGenerator\BasePolicy::class,
    'base_policy_file' => 'BasePolicy.php',
];
