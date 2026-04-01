<?php

namespace MrCatz\DataTable\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MrcatzMakeCommand extends Command
{
    protected $signature = 'mrcatz:make
        {name : Nama resource (contoh: User, Product, Category)}
        {--table= : Nama tabel database (default: nama resource di-pluralize)}
        {--path=Admin : Path sub-folder di dalam Livewire (default: Admin)}
        {--force : Overwrite file yang sudah ada}';

    protected $description = 'Generate MrCatz DataTable page, table, dan blade views';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = Str::studly($this->option('path'));
        $table = $this->option('table') ?? Str::snake(Str::plural($name));
        $lower = Str::lower($name);
        $kebab = Str::kebab($name);
        $snake = Str::snake($name);

        $replacements = [
            '{{name}}' => $name,
            '{{path}}' => $path,
            '{{lower}}' => $lower,
            '{{kebab}}' => $kebab,
            '{{snake}}' => $snake,
            '{{table}}' => $table,
            '{{pathLower}}' => Str::lower($path),
        ];

        $files = [
            [
                'stub' => 'page.stub',
                'dest' => app_path("Livewire/{$path}/{$name}/{$name}Page.php"),
                'label' => "Livewire/{$path}/{$name}/{$name}Page.php",
            ],
            [
                'stub' => 'table.stub',
                'dest' => app_path("Livewire/{$path}/{$name}/{$name}Table.php"),
                'label' => "Livewire/{$path}/{$name}/{$name}Table.php",
            ],
            [
                'stub' => 'page-blade.stub',
                'dest' => resource_path("views/livewire/" . Str::lower($path) . "/{$kebab}/{$kebab}-page.blade.php"),
                'label' => "views/livewire/" . Str::lower($path) . "/{$kebab}/{$kebab}-page.blade.php",
            ],
            [
                'stub' => 'form-blade.stub',
                'dest' => resource_path("views/livewire/" . Str::lower($path) . "/{$kebab}/{$snake}_form.blade.php"),
                'label' => "views/livewire/" . Str::lower($path) . "/{$kebab}/{$snake}_form.blade.php",
            ],
        ];

        $created = 0;

        foreach ($files as $file) {
            $stubPath = __DIR__ . '/../../stubs/' . $file['stub'];

            if (!file_exists($stubPath)) {
                $this->error("Stub not found: {$file['stub']}");
                continue;
            }

            if (file_exists($file['dest']) && !$this->option('force')) {
                $this->warn("SKIP  {$file['label']} (sudah ada, gunakan --force untuk overwrite)");
                continue;
            }

            $dir = dirname($file['dest']);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $content = file_get_contents($stubPath);
            foreach ($replacements as $search => $replace) {
                $content = str_replace($search, $replace, $content);
            }

            file_put_contents($file['dest'], $content);
            $this->info("CREATE  {$file['label']}");
            $created++;
        }

        $this->newLine();
        if ($created > 0) {
            $this->info("{$created} file berhasil di-generate!");
            $this->newLine();
            $this->line("Langkah selanjutnya:");
            $this->line("  1. Tambahkan route: Route::get('/{$kebab}', {$name}Page::class)->name('{$snake}')");
            $this->line("  2. Edit baseQuery() di {$name}Table.php sesuai tabel database");
            $this->line("  3. Edit kolom di setTable() dan form fields di {$snake}_form.blade.php");
        } else {
            $this->warn("Tidak ada file yang di-generate.");
        }

        return self::SUCCESS;
    }
}
