<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (config('system.default.load_config_from_database')) {
            $this->overrideConfigValues();
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Override config with value from database.
     *
     * @return void
     */
    protected function overrideConfigValues(): void
    {
        if (Schema::hasTable('settings')) {
            $data = DB::table('settings')
                ->where('key', 'LIKE', 'system.%')
                ->orWhere('key', 'LIKE', 'logging.%')
                ->orWhere('key', 'LIKE', 'filesystems.%')
                ->orWhere('key', 'LIKE', 'microservice.%')
                ->get();

            /** @var Setting $d */
            foreach ($data as $d) {
                // Decrypt value, as we're not using Eloquent ORM here
                $d->value = stringEncryption('decrypt', $d->value);
                if ($d->key === 'system.default.users.allow_email_change') {
                    //                    ray([
                    //                        $d->value,
                    //                        is_null($d->value),
                    //                    ]);
                }
                config([$d->key => $d->value]);
                if (! is_null($d->value)) {
                    config([$d->key => $d->value]);
                }
            }

            $this->rewriteDiskConfig();
        }
    }

    /**
     * @return void
     */
    protected function rewriteDiskConfig(): void
    {
        config([
            'filesystems.disks.s3-uploads' => [
                'driver' => 's3',
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
                'region' => config('filesystems.disks.s3.region'),
                'bucket' => config('filesystems.disks.s3.bucket'),
                'url' => config('filesystems.disks.s3.url'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'root' => 'uploads',
            ],
        ]);

        config([
            'filesystems.disks.backups' => [
                'driver' => 's3',
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
                'region' => config('filesystems.disks.s3.region'),
                'bucket' => config('filesystems.disks.s3.bucket'),
                'url' => config('filesystems.disks.s3.url'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'root' => 'backups',
            ],
        ]);
    }
}
