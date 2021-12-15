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
    protected $signature = "make:repositories 
        {--c|contracts} 
        {--p|policies} 
        {--mn|models-namespace= : The path to the models you want to generate the repositories for} 
        {--cn|contracts-namespace= : The path where we'll generate the contracts} 
        {--rn|repositories-namespace= : The path where we'll generate the repositories} 
        {--pn|policies-namespace= : The path where we'll generate the policies}";

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
    protected bool $override = false;
    protected ?string $repositoriesNamespace = null;
    protected ?string $contractsNamespace = null;
    protected ?string $policiesNamespace = null;
    protected ?string $modelsNamespace = null;
    protected array $directories = [];
    protected array $namespaces = [];
    protected array $models = [];
    protected bool $hasContracts = false;
    protected bool $hasPolicies = false;

    public function __construct()
    {
        parent::__construct();

        $this->directories = [
            'contracts' => config('repository-generator.contracts_directory'),
            'repositories' => config('repository-generator.repositories_directory'),
            'policies' => config('repository-generator.policies_directory'),
            'models' => config('repository-generator.models_directory')
        ];

        $this->namespaces = [
            'contracts' => config('repository-generator.contracts_namespace'),
            'repositories' => config('repository-generator.repositories_namespace'),
            'policies' => config('repository-generator.policies_namespace'),
            'models' => config('repository-generator.models_namespace')
        ];
    }

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

        $this->modelsNamespace = $this->option('models-namespace');

        // Get all model file names.
        $this->models = $this->getModels();

        // Check model files.
        if (count($this->models) === 0) {
            $this->noModelsMessage();
        }

        if ($this->hasContracts = $this->option('contracts')) {
            // Check contracts folder permissions.
            $this->checkContractsPermissions();

            $this->createContracts();
        }

        if ($this->hasPolicies = $this->option('policies')) {
            // Check if policies are required.
            $this->checkPoliciesPermissions();

            $this->createPolicies();
        }

        $this->createRepositories();
    }

    /**
     * Get all model names from models directory.
     *
     * @return array
     */
    private function getModels(): array
    {
        if ($this->modelsNamespace !== null) {
            $this->namespaces['models'] = $this->generateNamespace($this->modelsNamespace);
            $modelsDirectory = rtrim($this->modelsNamespace, '/');
            if (substr($modelsDirectory, -1) !== '\\') {
                $modelsDirectory .= '\\';
            }
            $this->directories['models'] = $modelsDirectory;
        }

        if (! is_dir($this->directories['models'])) {
            $this->error('The models directory does not exist.');
            exit;
        }

        $models = glob($this->directories['models'] . '*.php');
        return str_replace([$this->directories['models'], '.php'], '', $models);
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
        $this->contractsNamespace = $this->option('contracts-namespace');
        if ($this->contractsNamespace !== null) {
            $this->namespaces['contracts'] = $this->generateNamespace($this->contractsNamespace);
            $this->directories['contracts'] = $this->fileNamespace($this->namespaces['contracts']);
        }

        return $this->directories['contracts'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Get policies path.
     *
     * @param null|string $path
     * @return string
     */
    private function policiesPath(string $path = null): string
    {
        $this->policiesNamespace = $this->option('policies-namespace');
        if ($this->policiesNamespace !== null) {
            $this->namespaces['policies'] = $this->generateNamespace($this->policiesNamespace);
            $this->directories['policies'] = $this->fileNamespace($this->namespaces['policies']);
        }

        return $this->directories['policies'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Get repositories path.
     *
     * @param null|string $path
     * @return string
     */
    private function repositoriesPath(string $path = null): string
    {
        $this->repositoriesNamespace = $this->option('repositories-namespace');
        if ($this->repositoriesNamespace !== null) {
            $this->namespaces['repositories'] = $this->generateNamespace($this->repositoriesNamespace);
            $this->directories['repositories'] = $this->fileNamespace($this->namespaces['repositories']);
        }

        return $this->directories['repositories'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param string $class
     * @return string|null
     */
    public function fileNamespace(string $class): ?string
    {
        if (in_array(mb_strtolower(substr($class, 0, 4)), ['app\\', 'app/'])) {
            return app_path(substr($class, 4));
        }

        return null;
    }

    /**
     * Get parent path of repository of interface folder.
     *
     * @param string $child
     * @return string
     */
    private function parentPath(string $child): string
    {
        if (! is_dir($child)) {
            mkdir($child, 0777, true);
        }

        return dirname($child);
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
        $repositoryParentPath = $this->parentPath($repositoriesPath);

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
        $contractsParentPath = $this->parentPath($contractsPath);

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
     * @throws FileException
     */
    private function checkPoliciesPermissions()
    {
        // Get full path of policies directory.
        $policiesPath = $this->policiesPath();

        // Get parent directory of policies path.
        $policiesParentPath = $this->parentPath($policiesPath);

        // Check parent of policies directory is writable.
        if (!file_exists($policiesPath) && !is_writable($policiesParentPath)) {
            throw FileException::notWritableDirectory($policiesParentPath);
        }

        // Check policies' directory permissions.
        if (file_exists($policiesPath) && !is_writable($policiesPath)) {
            throw FileException::notWritableDirectory($policiesPath);
        }
    }

    /**
     * @param string $folder
     * @return void
     */
    private function createFolder(string $folder): void
    {
        if (! file_exists($folder)) {
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

    protected function createPolicies()
    {
        // Create policies folder if it's necessary.
        $this->createFolder($this->directories['policies']);

        // Get existing policy file names.
        $existingPolicyFiles = glob($this->policiesPath('*.php'));

        // Remove main policy file name from array
        $existingPolicyFiles = array_diff(
            $existingPolicyFiles,
            [$this->policiesPath(config('repository-generator.base_policy_file'))]
        );

        // Ask for overriding, If there are files in policies directory.
        if (count($existingPolicyFiles) > 0 && ! $this->override) {
            if ($this->confirm('Do you want to overwrite the existing files? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $policyStub = $this->getStub('Policy');

        // Policy stub values those should be changed by command.
        $policyStubValues = [
            '{{ use_statement_for_user_model }}',
            '{{ policies_namespace }}',
            '{{ policy }}',
            '{{ models_namespace }}',
            '{{ model }}',
            '{{ modelVariable }}'
        ];

        foreach ($this->models as $model) {
            $policy = $model . 'Policy';

            // Current policy file name
            $policyFile = $this->policiesPath($policy . '.php');

            // User Model
            $userClass = config('repository-generator.user_class');
            $useStatementForUserModel = false;

            if (class_exists($userClass)) {
                $useStatementForUserModel = 'use ' . $userClass . ';';
            }

            // Fillable policy values for generating real files
            $policyValues = [
                $useStatementForUserModel ?: '',
                $this->namespaces['policies'],
                $policy,
                $this->namespaces['models'],
                $model,
                mb_strtolower($model)
            ];

            // Generate body of the policy file
            $policyContent = str_replace(
                $policyStubValues,
                $policyValues,
                $policyStub
            );

            if (in_array($policyFile, $existingPolicyFiles)) {
                if ($this->override) {
                    $this->writeFile($policyFile, $policyContent);
                    $this->info('Overridden policy file: ' . $policy);
                }
            } else {
                $this->writeFile($policyFile, $policyContent);
                $this->info('Created policy file: ' . $policy);
            }

            $this->override = false;
        }
    }

    protected function createRepositories()
    {
        // Create repositories folder if it's necessary.
        $this->createFolder($this->directories['repositories']);

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
        $repositoryStub = $this->getStub($this->hasContracts ? 'RepositoryEloquent' : 'Repository');

        // Repository stub values those should be changed by command.
        $repositoryStubValues = [
            '{{ use_statement_for_repository }}',
            '{{ repositories_namespace }}',
            '{{ base_repository }}',
            '{{ repository }}',
            '{{ models_namespace }}',
            '{{ model }}'
        ];

        if ($this->hasContracts) {
            $repositoryStubValues[] = '{{ use_statement_for_contract }}';
        }

        foreach ($this->models as $model) {
            $repository = $model . ($this->hasContracts ? 'RepositoryEloquent' : 'Repository');

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
            if ($this->hasContracts) {
                // Current repository file name
                $contractFile = $this->contractsPath($model . 'Repository.php');

                if (is_file($contractFile)) {
                    $mainContract = $this->namespaces['contracts'];
                    $useStatementForContract = 'use ' . $mainContract . '\\' . $model . 'Repository;';
                }
            }

            // Fillable repository values for generating real files
            $repositoryValues = [
                $useStatementForRepository ?: '',
                $this->namespaces['repositories'],
                str_replace('.php', '', config('repository-generator.base_repository_file')),
                $repository,
                $this->namespaces['models'],
                $model
            ];

            if ($this->hasContracts) {
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

            $this->override = false;
        }
    }

    /**
     * @param string $namespace
     * @return string
     */
    public function generateNamespace(string $namespace): string
    {
        return ucwords(str_replace('/', '\\', $namespace), '\\');
    }

    protected function createContracts()
    {
        $this->contractsNamespace = $this->option('contracts-namespace');
        if ($this->contractsNamespace !== null) {
            $this->contractsNamespace = $this->generateNamespace($this->contractsNamespace);
            $this->namespaces['contracts'] = $this->contractsNamespace;
        }

        // Create contracts folder if it's necessary.
        $this->createFolder($this->namespaces['contracts']);

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
            '{{ use_statement_for_contract }}',
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
                $this->generateNamespace($this->namespaces['contracts']),
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

            $this->override = false;
        }
    }
}
