<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeEnum extends Command
{
    protected $signature = 'make:enum {name : The name of the enum class}';
    protected $description = 'Create a new enum class';

    public function handle(Filesystem $files)
    {
        $name = $this->argument('name');
        $path = app_path("Enums/{$name}.php");

        // Check if file already exists
        if ($files->exists($path)) {
            $this->error("Enum {$name} already exists!");
            return 1;
        }

        // Create the Enums directory if it doesn't exist
        $files->ensureDirectoryExists(app_path('Enums'));

        // Build the enum content
        $stub = $this->getStub();
        $content = str_replace('{{ class }}', $name, $stub);

        // Write the file
        $files->put($path, $content);

        $this->info("Enum {$name} created successfully at app/Enums/{$name}.php");
        return 0;
    }

    protected function getStub(): string
    {
        return <<<PHP
<?php

namespace App\Enums;

enum {{ class }}
{
    case DRAFT;
    case PUBLISHED;
}
PHP;
    }
}
