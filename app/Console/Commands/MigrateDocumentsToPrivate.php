<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MigrateDocumentsToPrivate extends Command
{
    protected $signature = 'rtlh:migrate-documents-to-private';
    protected $description = 'Migrate sensitive RTLH documents (KK, Akta, Surat Tanah, Proposals) from public to private disk';

    public function handle(): int
    {
        $publicBase = storage_path('app/public/rtlh');
        $privateBase = storage_path('app/private/rtlh');

        $folders = ['proposals', 'documents'];
        $movedCount = 0;

        foreach ($folders as $folder) {
            $srcDir = $publicBase . DIRECTORY_SEPARATOR . $folder;
            $dstDir = $privateBase . DIRECTORY_SEPARATOR . $folder;

            if (! File::exists($srcDir)) {
                continue;
            }

            if (! File::exists($dstDir)) {
                File::makeDirectory($dstDir, 0755, true);
            }

            $allFiles = File::allFiles($srcDir);
            foreach ($allFiles as $file) {
                $relativeSubPath = substr($file->getPathname(), strlen($srcDir) + 1);
                $targetFile = $dstDir . DIRECTORY_SEPARATOR . $relativeSubPath;
                $targetParent = dirname($targetFile);

                if (! File::exists($targetParent)) {
                    File::makeDirectory($targetParent, 0755, true);
                }

                if (! File::exists($targetFile)) {
                    File::copy($file->getPathname(), $targetFile);
                    File::delete($file->getPathname());
                    $movedCount++;
                    $this->info("Moved: {$folder}/{$relativeSubPath} -> private");
                }
            }
        }

        $this->info("Migrasi selesai. Total {$movedCount} berkas dipindahkan ke storage/app/private.");
        return 0;
    }
}
