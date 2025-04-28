<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name} {--force}';
    protected $description = 'Create a new service class and its interface';

    public function handle()
    {
        $name = $this->argument('name');

        if (!preg_match('/^[A-Z][A-Za-z0-9]+$/', $name)) {
            $this->error("Invalid service name. Use PascalCase without spaces or symbols.");
            return;
        }

        $interfaceName = "{$name}Interface";
        $className = $name;

        // Directories
        $interfaceDir = app_path('Services/Interfaces');
        $serviceDir = app_path('Services');

        // Paths
        $interfacePath = "$interfaceDir/{$interfaceName}.php";
        $servicePath = "$serviceDir/{$className}.php";

        // Create directories if they don't exist
        File::ensureDirectoryExists($interfaceDir);
        File::ensureDirectoryExists($serviceDir);

        // Generate interface
        if (!File::exists($interfacePath) || $this->option('force') || $this->confirm("The interface {$interfaceName} already exists. Overwrite?", false)) {
            File::put($interfacePath, $this->buildInterfaceContent($interfaceName));
            $this->info("Interface {$interfaceName} created successfully.");
        } else {
            $this->warn("Interface {$interfaceName} already exists.");
        }

        // Generate service class
        if (!File::exists($servicePath) || $this->option('force') || $this->confirm("The service class {$className} already exists. Overwrite?", false)) {
            File::put($servicePath, $this->buildServiceContent($className, $interfaceName));
            $this->info("Service class {$className} created successfully.");
        } else {
            $this->warn("Service class {$className} already exists.");
        }

        // Suggest binding
        $this->line("\nAdd the following binding to your AppServiceProvider:");
        $this->line("    \$this->app->bind(\\App\\Services\\Interfaces\\{$interfaceName}::class, \\App\\Services\\{$className}::class);");
    }

    protected function buildInterfaceContent(string $interfaceName): string
    {
        return <<<PHP
<?php

namespace App\Services\Interfaces;

interface {$interfaceName}
{
    // Define your service interface methods here
}
PHP;
    }

    protected function buildServiceContent(string $className, string $interfaceName): string
    {
        return <<<PHP
<?php

namespace App\Services;

use App\Services\Interfaces\\{$interfaceName};

class {$className} implements {$interfaceName}
{
    // Implement your service methods here
}
PHP;
    }
}
