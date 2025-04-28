<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name} {--force}';
    protected $description = 'Create a new Eloquent repository and its interface';

    public function handle()
    {
        $name = $this->argument('name');

        if (!preg_match('/^[A-Z][A-Za-z0-9]+$/', $name)) {
            $this->error("Invalid repository name. Use PascalCase without spaces or symbols.");
            return;
        }

        $interfaceName = "{$name}RepositoryInterface";
        $className = "Eloquent{$name}Repository";

        // Direktori
        $interfaceDir = app_path('Repositories/Interfaces');
        $repositoryDir = app_path('Repositories/Eloquent');

        // Path file
        $interfacePath = "$interfaceDir/{$interfaceName}.php";
        $repositoryPath = "$repositoryDir/{$className}.php";

        // Buat folder jika belum ada
        File::ensureDirectoryExists($interfaceDir);
        File::ensureDirectoryExists($repositoryDir);

        // Generate Interface
        if (!File::exists($interfacePath) || $this->option('force') || $this->confirm("The interface {$interfaceName} already exists. Overwrite?", false)) {
            File::put($interfacePath, $this->buildInterfaceContent($interfaceName));
            $this->info("Interface {$interfaceName} created successfully.");
        } else {
            $this->warn("Interface {$interfaceName} already exists.");
        }

        // Generate Repository
        if (!File::exists($repositoryPath) || $this->option('force') || $this->confirm("The repository {$className} already exists. Overwrite?", false)) {
            File::put($repositoryPath, $this->buildRepositoryContent($className, $interfaceName, $name));
            $this->info("Repository {$className} created successfully.");
        } else {
            $this->warn("Repository {$className} already exists.");
        }

        // Saran untuk binding
        $this->line("\nAdd the following binding to your AppServiceProvider:");
        $this->line("    \$this->app->bind(\\App\\Repositories\\Interfaces\\{$interfaceName}::class, \\App\\Repositories\\Eloquent\\{$className}::class);");
    }

    protected function buildInterfaceContent(string $interfaceName): string
    {
        return <<<PHP
<?php

namespace App\Repositories\Interfaces;

interface {$interfaceName}
{
    public function all();
    public function find(int \$id);
    public function create(array \$data);
    public function update(int \$id, array \$data);
    public function delete(int \$id);
}
PHP;
    }

    protected function buildRepositoryContent(string $className, string $interfaceName, string $modelName): string
    {
        $modelVar = lcfirst($modelName);
        return <<<PHP
<?php

namespace App\Repositories\Eloquent;

use App\Models\\{$modelName}Model;
use App\Repositories\Interfaces\\{$interfaceName};

class {$className} implements {$interfaceName}
{
    protected \${$modelVar};

    public function __construct({$modelName}Model \${$modelVar})
    {
        \$this->{$modelVar} = \${$modelVar};
    }

    public function all()
    {
        return \$this->{$modelVar}->all();
    }

    public function find(int \$id)
    {
        return \$this->{$modelVar}->find(\$id);
    }

    public function create(array \$data)
    {
        return \$this->{$modelVar}->create(\$data);
    }

    public function update(int \$id, array \$data)
    {
        \$model = \$this->{$modelVar}->find(\$id);
        return \$model ? \$model->update(\$data) : false;
    }

    public function delete(int \$id)
    {
        \$model = \$this->{$modelVar}->find(\$id);
        return \$model ? \$model->delete() : false;
    }
}
PHP;
    }
}
