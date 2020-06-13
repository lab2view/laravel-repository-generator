<?php

namespace Lab2view\RepositoryGenerator\Console\Commands;

use Illuminate\Console\Command;
use Lab2View\RepositoryGenerator\Exceptions\FileException;
use Lab2View\RepositoryGenerator\Exceptions\StubException;

class Generate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repositories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generating repository from existing model files';

    /**
     * Overriding existing files.
     *
     * @var bool
     */
    protected $override = false;

    /**
     * Execute the console command.
     *
     * @return void
     * @throws FileException
     * @throws StubException
     */
    public function handle()
    {
        // Check repository folder permissions.
        $this->checkRepositoryPermissions();

        // Create repository folder if it's necessary.
        $this->createFolder(config('repository-generator.repository_directory'));

        // Get all model file names.
        $models = $this->getModels();

        // Check model files.
        if (count($models) === 0) {
            $this->noModelsMessage();
        }

        // Get existing repository file names.
        $existingRepositoryFiles = glob($this->repositoryPath('*.php'));

        // Remove main repository file name from array
        $existingRepositoryFiles = array_diff(
            $existingRepositoryFiles,
            [$this->repositoryPath(config('repository-generator.base_repository_file'))]
        );

        // Ask for overriding, If there are files in repositories.
        if (count($existingRepositoryFiles) > 0 && !$this->override) {
            if ($this->confirm('Do you want to overwrite the existing files? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $repositoryStub = $this->getStub('Repository');

        // Repository stub values those should be changed by command.
        $repositoryStubValues = [
            '{{ user_statement_for_repository }}',
            '{{ repository_namespace }}',
            '{{ base_repository }}',
            '{{ repository }}',
            '{{ mode_namespace }}',
            '{{ model }}'
        ];

        foreach ($models as $model) {
            $repository = $model . 'Repository';

            // Current repository file name
            $repositoryFile = $this->repositoryPath($repository . '.php');

            // Check main repository file's path to add use
            $useStatementForRepository = false;
            if (dirname($repositoryFile) !== dirname(config('repository-generator.base_repository_file'))
            ) {
                $mainRepository = config('repository-generator.base_repository_class');
                $useStatementForRepository = 'use ' . $mainRepository . ';';
            }

            // Fillable repository values for generating real files
            $repositoryValues = [
                $useStatementForRepository ? $useStatementForRepository : '',
                config('repository-generator.repository_namespace'),
                str_replace('.php', '', config('repository-generator.base_repository_file')),
                $repository,
                config('repository-generator.model_namespace'),
                $model
            ];

            // Generate body of the repository file
            $repositoryContent = str_replace(
                $repositoryStubValues,
                $repositoryValues,
                $repositoryStub);

            if (in_array($repositoryFile, $existingRepositoryFiles)) {
                if ($this->override) {
                    $this->writeFile($repositoryFile, $repositoryContent);
                    $this->info('Overridden repository file: ' . $repository);
                }
            } else {
                $this->writeFile($repositoryFile, $repositoryContent);
                $this->info('Created repository file: ' . $repository);
            }
        }
    }

    /**
     * Get all model names from models directory.
     *
     * @return array|mixed
     */
    private function getModels()
    {
        $modelDirectory = config('repository-generator.model_directory');
        $models = glob($modelDirectory . '*');
        $models = str_replace([$modelDirectory, '.php'], '', $models);

        return $models;
    }

    /**
     * Get stub content.
     *
     * @param $file
     * @return bool|string
     * @throws StubException
     */
    private function getStub($file)
    {
        $stub = __DIR__ . '/../Stubs/' . $file . '.stub';
        if (file_exists($stub)) {
            return file_get_contents($stub);
        }
        throw StubException::fileNotFound($file);
    }

    /**
     * Get repository path.
     *
     * @param null $path
     * @return string
     */
    private function repositoryPath($path = null)
    {
        return config('repository-generator.repository_directory') . $path;
    }

    /**
     * Get parent path of repository of interface folder.
     *
     * @param string $child
     * @return string
     */
    private function parentPath($child = 'repository')
    {
        $childPath = $child . 'Path';
        $childPath = $this->$childPath();

        return dirname($childPath);
    }

    /**
     * Generate/override a file.
     *
     * @param $file
     * @param $content
     */
    private function writeFile($file, $content)
    {
        file_put_contents($file, $content);
    }

    /**
     * Check repository folder permissions.
     *
     * @throws FileException
     */
    private function checkRepositoryPermissions()
    {
        // Get full path of repository directory.
        $repositoryPath = $this->repositoryPath();

        // Get parent directory of repository path.
        $repositoryParentPath = $this->parentPath('repository');

        // Check parent of repository directory is writable.
        if (!file_exists($repositoryPath) && !is_writable($repositoryParentPath)) {
            throw FileException::notWritableDirectory($repositoryParentPath);
        }

        // Check repository directory permissions.
        if (file_exists($repositoryPath) && !is_writable($repositoryPath)) {
            throw FileException::notWritableDirectory($repositoryPath);
        }
    }

    /**
     * @param $folder
     */
    private function createFolder($folder)
    {
        if (!file_exists($folder)) {
            mkdir($folder);
        }
    }

    /**
     * Show message and stop script, If there are no model files to work.
     */
    private function noModelsMessage()
    {
        $this->warn('Repository generator has stopped!');
        $this->line(
            'There are no model files to use in directory: "'
            . config('repository-generator.model_directory')
            . '"'
        );
        exit;
    }
}
