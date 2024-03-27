<?php

namespace App\Http\Controllers\Extend;

use Backpack\BackupManager\app\Http\Controllers\BackupController as BC;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Spatie\Permission\Exceptions\UnauthorizedException;

class BackupController extends BC
{
    public function index()
    {
        if (! count(config('backup.backup.destination.disks'))) {
            abort(500, trans('backpack::backup.no_disks_configured'));
        }

        if (auth()->guest() || ! auth()->user()->hasRole('System Administrators')) {
            throw UnauthorizedException::forRoles(['System Administrators']);
        }

        $this->data['backups'] = [];

        foreach (config('backup.backup.destination.disks') as $diskName) {
            $disk = Storage::disk($diskName);
            $files = $disk->allFiles();

            // make an array of backup files, with their filesize and creation date
            foreach ($files as $file) {
                // remove diskname from filename
                $fileName = str_replace('backups/', '', $file);
                $downloadLink = route('backup.download', ['file_name' => $fileName, 'disk' => $diskName]);
                $deleteLink = route('backup.destroy', ['file_name' => $fileName, 'disk' => $diskName]);

                // only take the zip files into account
                if (substr($file, -4) == '.zip' && $disk->exists($file)) {
                    $this->data['backups'][] = (object) [
                        'filePath' => $file,
                        'fileName' => $fileName,
                        'fileSize' => round((int) $disk->size($file) / 1048576, 2),
                        'lastModified' => Carbon::createFromTimeStamp($disk->lastModified($file))->formatLocalized(
                            '%d %B %Y, %H:%M'
                        ),
                        'diskName' => $diskName,
                        'downloadLink' => is_a(
                            $disk->getAdapter(),
                            LocalFilesystemAdapter::class,
                            true
                        ) ? $downloadLink : null,
                        'deleteLink' => $deleteLink,
                    ];
                }
            }
        }

        // reverse the backups, so the newest one would be on top
        $this->data['backups'] = array_reverse($this->data['backups']);
        $this->data['title'] = trans('backpack::backup.backups');

        return view('backupmanager::backup', $this->data);
    }

    /**
     * Downloads a backup zip file.
     */
    public function download()
    {
        $diskName = $this->getDisk();
        $fileName = request()->input('file_name');

        if (blank($fileName)) {
            abort(500, 'Please specify a valid backup file name.');
        }

        if (blank($diskName)) {
            abort(500, 'Please specify a valid backup disk.');
        }

        $disk = Storage::disk($diskName);

        if (! $this->isBackupDisk($diskName)) {
            abort(500, trans('backpack::backup.unknown_disk'));
        }

        if (! is_a($disk->getAdapter(), LocalFilesystemAdapter::class, true)) {
            abort(404, trans('backpack::backup.only_local_downloads_supported'));
        }

        if (! $disk->exists($fileName)) {
            abort(404, trans('backpack::backup.backup_doesnt_exist'));
        }

        return $disk->download($fileName);
    }

    /**
     * Deletes a backup file.
     */
    public function delete()
    {
        $diskName = $this->getDisk();
        $fileName = request()->input('file_name');

        if (blank($fileName)) {
            abort(500, 'Please specify a valid backup file name.');
        }

        if (blank($diskName)) {
            abort(500, 'Please specify a valid backup disk.');
        }

        if (! $this->isBackupDisk($diskName)) {
            return response(trans('backpack::backup.unknown_disk'), 500);
        }

        $disk = Storage::disk($diskName);

        if (! $disk->exists($fileName)) {
            return response(trans('backpack::backup.backup_doesnt_exist'), 404);
        }

        return $disk->delete($fileName);
    }

    /**
     * Check if disk is a backup disk.
     *
     * @param  string  $diskName
     * @return bool
     */
    private function isBackupDisk(string $diskName): bool
    {
        return in_array($diskName, config('backup.backup.destination.disks'), true);
    }

    /**
     * @return string
     */
    private function getDisk(): string
    {
        $diskName = request()->input('disk');

        if (blank($diskName)) {
            $defaultDisk = config('backup.backup.destination.disks');
            if (count($defaultDisk) === 0) {
                abort(500, 'Please specify a valid backup disk.');
            }

            $diskName = $defaultDisk[0];
        }

        return $diskName;
    }
}
