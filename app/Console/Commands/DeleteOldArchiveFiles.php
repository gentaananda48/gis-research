<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class DeleteOldArchiveFiles extends Command
{
    protected $signature = 'archive:delete-old-files';
    protected $description = 'Delete old folders from the archive directory';

    public function handle()
    {
        $archivePath = public_path('upload/archive');
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        if (File::isDirectory($archivePath)) {
            $folders = File::directories($archivePath);
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                $folderDate = Carbon::createFromFormat('Ymd', $folderName)->startOfMonth();

                if ($folderDate->lessThan($twoMonthsAgo)) {
                    File::deleteDirectory($folder);
                    $this->info('Deleted folder: ' . $folderName);
                }
            }
        } else {
            $this->info('The directory does not exist: ' . $archivePath);
        }
    }
}
