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
        $failed = 0;

        // List the tmp directory once. The listing response (e.g. S3 ListObjectsV2)
        // already carries each file's last-modified time, so we avoid a per-file
        // HeadObject/metadata call — that call can 403 on S3-compatible disks whose
        // policy permits listing but not HEAD on the object, and it's N requests slower.
        foreach ($storage->listContents($tmpPath, false) as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $file = $item->path();

            try {
                $timestamp = $item->lastModified()
                    ?? $storage->lastModified($file);

                if (Carbon::createFromTimestamp($timestamp)->lt($cutoff)) {
                    $storage->delete($file);
                    $deleted++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->warn("Skipped {$file}: {$e->getMessage()}");
            }
        }

        $this->info("Deleted {$deleted} expired temporary editor image(s).");

        if ($failed > 0) {
            $this->warn("Skipped {$failed} file(s) due to storage errors.");
        }

        return self::SUCCESS;
    }
}
