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
    protected $signature = 'make:repositories {--c|contracts}';

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

    private $hasContract = false;
    protected $models = [];

    /**
     * Execute the console command.
     *
     * @return void
     * @throws FileException
     * @throws StubException
     */
    public function handle()
    {
        // Check repositories' folder permissions.
        $this->checkRepositoriesPermissions();

        // Get all model file names.
        $this->models = $this->getModels();

        // Check model files.
        if (count($this->models) === 0) {
            $this->noModelsMessage();
        }

        if ($this->hasContract = $this->option('contracts')) {
            // Check contracts folder permissions.
            $this->checkContractsPermissions();

            $this->createContracts();
        }

        $this->createRepositories();
    }

    /**
     * Get all model names from models directory.
     *
     * @param string|null $path
     * @return array
     */
    private function getModels(string $path = null)
    {
        $modelsDirectory = $path ?? config('repository-generator.models_directory');
        $models = glob($modelsDirectory . '*');
        return str_replace([$modelsDirectory, '.php'], '', $models);
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
     * Get contracts path.
     *
     * @param null|string $path
     * @return string
     */
    private function contractsPath(string $path = null): string
    {
        return config('repository-generator.contracts_directory') . $path;
    }

    /**
     * Get repositories path.
     *
     * @param null|string $path
     * @return string
     */
    private function repositoriesPath(string $path = null): string
    {
        return config('repository-generator.repositories_directory') . $path;
    }

    /**
     * Get parent path of repository of interface folder.
     *
     * @param string $child
     * @return string
     */
    private function parentPath(string $child = 'repositories'): string
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
     * Check repositories' folder permissions.
     *
     * @throws FileException
     */
    private function checkRepositoriesPermissions()
    {
        // Get full path of repository directory.
        $repositoriesPath = $this->repositoriesPath();

        // Get parent directory of repository path.
        $repositoryParentPath = $this->parentPath();

        // Check parent of repository directory is writable.
        if (!file_exists($repositoriesPath) && !is_writable($repositoryParentPath)) {
            throw FileException::notWritableDirectory($repositoryParentPath);
        }

        // Check repository directory permissions.
        if (file_exists($repositoriesPath) && !is_writable($repositoriesPath)) {
            throw FileException::notWritableDirectory($repositoriesPath);
        }
    }

    /**
     * Check repository folder permissions.
     *
     * @throws FileException
     */
    private function checkContractsPermissions()
    {
        // Get full path of contracts directory.
        $contractsPath = $this->contractsPath();

        // Get parent directory of contracts path.
        $contractsParentPath = $this->parentPath('contracts');

        // Check parent of contracts directory is writable.
        if (!file_exists($contractsPath) && !is_writable($contractsParentPath)) {
            throw FileException::notWritableDirectory($contractsParentPath);
        }

        // Check contracts directory permissions.
        if (file_exists($contractsPath) && !is_writable($contractsPath)) {
            throw FileException::notWritableDirectory($contractsPath);
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
            . config('repository-generator.models_directory')
            . '"'
        );
        exit;
    }

    protected function createRepositories()
    {
        // Create repositories folder if it's necessary.
        $this->createFolder(config('repository-generator.repositories_directory'));

        // Get existing repository file names.
        $existingRepositoryFiles = glob($this->repositoriesPath('*.php'));

        // Remove main repository file name from array
        $existingRepositoryFiles = array_diff(
            $existingRepositoryFiles,
            [$this->repositoriesPath(config('repository-generator.base_repository_file'))]
        );

        // Ask for overriding, If there are files in repositories directory.
        if (count($existingRepositoryFiles) > 0 && ! $this->override) {
            if ($this->confirm('Do you want to overwrite the existing files? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $repositoryStub = $this->getStub($this->hasContract ? 'RepositoryEloquent' : 'Repository');

        // Repository stub values those should be changed by command.
        $repositoryStubValues = [
            '{{ user_statement_for_repository }}',
            '{{ repositories_namespace }}',
            '{{ base_repository }}',
            '{{ repository }}',
            '{{ models_namespace }}',
            '{{ model }}'
        ];

        if ($this->hasContract) {
            $repositoryStubValues[] = '{{ user_statement_for_contract }}';
        }

        foreach ($this->models as $model) {
            $repository = $model . ($this->hasContract ? 'RepositoryEloquent' : 'Repository');

            // Current repository file name
            $repositoryFile = $this->repositoriesPath($repository . '.php');

            // Check main repository file's path to add use
            $useStatementForRepository = false;
            if (dirname($repositoryFile) !== dirname(config('repository-generator.base_repository_file'))) {
                $mainRepository = config('repository-generator.base_repository_class');
                $useStatementForRepository = 'use ' . $mainRepository . ';';
            }

            // Check main repository file's path to add use
            $useStatementForContract = false;
            if ($this->hasContract) {
                // Current repository file name
                $contractFile = $this->contractsPath($model . 'Repository.php');

                if (is_file($contractFile)) {
                    $mainContract = config('repository-generator.contracts_namespace');
                    $useStatementForContract = 'use ' . $mainContract . '\\' . $model . 'Repository;';
                }
            }

            // Fillable repository values for generating real files
            $repositoryValues = [
                $useStatementForRepository ?: '',
                config('repository-generator.repositories_namespace'),
                str_replace('.php', '', config('repository-generator.base_repository_file')),
                $repository,
                config('repository-generator.models_namespace'),
                $model
            ];

            if ($this->hasContract) {
                $repositoryValues[] = $useStatementForContract ?: '';
            }

            // Generate body of the repository file
            $repositoryContent = str_replace(
                $repositoryStubValues,
                $repositoryValues,
                $repositoryStub
            );

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

    protected function createContracts()
    {
        // Create contracts folder if it's necessary.
        $this->createFolder(config('repository-generator.contracts_directory'));

        // Get existing contract file names.
        $existingContractFiles = glob($this->contractsPath('*.php'));

        // Remove main contract file name from array
        $existingContractFiles = array_diff(
            $existingContractFiles,
            [$this->contractsPath(config('repository-generator.base_contract_file'))]
        );

        // Ask for overriding, If there are files in contracts directory.
        if (count($existingContractFiles) > 0 && ! $this->override) {
            if ($this->confirm('Do you want to overwrite the existing files? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $contractStub = $this->getStub('Contract');

        // Contract stub values those should be changed by command.
        $contractStubValues = [
            '{{ user_statement_for_contract }}',
            '{{ contracts_namespace }}',
            '{{ base_contract }}',
            '{{ contract }}'
        ];

        foreach ($this->models as $model) {
            $contract = $model . 'Repository';

            // Current contract file name
            $contractFile = $this->contractsPath($contract . '.php');

            // Check main contract file's path to add use
            $useStatementForContract = false;
            if (dirname($contractFile) !== dirname(config('repository-generator.base_contract_file'))) {
                $mainContract = config('repository-generator.base_contract_interface');
                $useStatementForContract = 'use ' . $mainContract . ';';
            }

            // Fillable contract values for generating real files
            $contractValues = [
                $useStatementForContract ?: '',
                config('repository-generator.contracts_namespace'),
                str_replace('.php', '', config('repository-generator.base_contract_file')),
                $contract
            ];

            // Generate body of the contract file
            $contractContent = str_replace(
                $contractStubValues,
                $contractValues,
                $contractStub
            );

            if (in_array($contractFile, $existingContractFiles)) {
                if ($this->override) {
                    $this->writeFile($contractFile, $contractContent);
                    $this->info('Overridden contract file: ' . $contract);
                }
            } else {
                $this->writeFile($contractFile, $contractContent);
                $this->info('Created contract file: ' . $contract);
            }
        }
    }
}
