<?php

namespace MrCatz\DataTable\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MrcatzCleanupEditorImagesCommand extends Command
{
    protected $signature = 'mrcatz:cleanup-editor-images';

    protected $description = 'Delete expired temporary images uploaded via the editor';

    public function handle(): int
    {
        $config = config('mrcatz.editor_image', []);

        if (($config['mode'] ?? 'base64') !== 'upload') {
            $this->info('Editor image mode is not "upload". Nothing to clean.');
            return self::SUCCESS;
        }

        $disk = $config['disk'] ?? 'public';
        $path = $config['path'] ?? 'editor-images';
        $lifetime = $config['tmp_lifetime'] ?? 24;
        $tmpPath = $path . '/tmp';

        $storage = Storage::disk($disk);

        if (!$storage->exists($tmpPath)) {
            $this->info('No tmp directory found. Nothing to clean.');
            return self::SUCCESS;
        }

        $cutoff = Carbon::now()->subHours($lifetime);
        $deleted = 0;

        foreach ($storage->files($tmpPath) as $file) {
            $lastModified = Carbon::createFromTimestamp($storage->lastModified($file));

            if ($lastModified->lt($cutoff)) {
                $storage->delete($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} expired temporary editor image(s).");

        return self::SUCCESS;
    }
}
