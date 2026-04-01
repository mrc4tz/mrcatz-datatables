<?php

namespace MrCatz\DataTable\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MrcatzRemoveCommand extends Command
{
    protected $signature = 'mrcatz:remove
        {name : Nama resource (contoh: Product, User)}
        {--path= : Path sub-folder di dalam Livewire (contoh: Admin, Dashboard)}
        {--force : Hapus tanpa konfirmasi}';

    protected $description = 'Hapus file MrCatz DataTable yang di-generate (page, table, dan blade views)';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $path = $this->option('path') ? Str::studly($this->option('path')) : '';

        $kebab = Str::kebab($name);
        $snake = Str::snake($name);
        $filePath = $path ? "{$path}/{$name}" : $name;
        $viewDir = $path ? strtolower($path) . "/{$kebab}" : $kebab;

        $files = [
            app_path("Livewire/{$filePath}/{$name}Page.php"),
            app_path("Livewire/{$filePath}/{$name}Table.php"),
            resource_path("views/livewire/{$viewDir}/{$kebab}-page.blade.php"),
            resource_path("views/livewire/{$viewDir}/{$snake}_form.blade.php"),
        ];

        $existing = array_filter($files, fn($f) => file_exists($f));

        if (empty($existing)) {
            $this->warn("Tidak ada file {$name} yang ditemukan.");
            return self::SUCCESS;
        }

        $this->info("File yang akan dihapus:");
        foreach ($existing as $file) {
            $this->line("  - " . str_replace(base_path() . '/', '', $file));
        }
        $this->newLine();

        if (!$this->option('force') && !$this->confirm("Hapus " . count($existing) . " file di atas?")) {
            $this->info("Dibatalkan.");
            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($existing as $file) {
            unlink($file);
            $this->info("DELETE  " . str_replace(base_path() . '/', '', $file));
            $deleted++;
        }

        // Hapus folder kosong
        $dirs = [
            app_path("Livewire/{$filePath}"),
            resource_path("views/livewire/{$viewDir}"),
        ];

        foreach ($dirs as $dir) {
            if (is_dir($dir) && count(scandir($dir)) === 2) {
                rmdir($dir);
            }
        }

        $this->newLine();
        $this->info("{$deleted} file berhasil dihapus.");

        return self::SUCCESS;
    }
}
