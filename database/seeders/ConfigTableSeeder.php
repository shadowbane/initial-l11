<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // clear cache to ensure current setting is not cached
        cache()->clear();

        foreach ($this->settingList() as $index => $setting) {
            $value = Setting::where('key', $setting['key'])->first();

            if ($value instanceof Setting) {
                $value['key'] = $setting['key'];
                $value['name'] = $setting['name'];
                $value['description'] = $setting['description'];
                $value['field'] = $setting['field'];
                $value['active'] = $setting['active'];
                $value['updated_at'] = $setting['updated_at'];

                Setting::where('key', $value['key'])
                    ->update([
                        'name' => $setting['name'],
                        'description' => $setting['description'],
                        'field' => $setting['field'],
                        'active' => $setting['active'],
                        'updated_at' => $setting['updated_at'],
                    ]);
            } else {
                Setting::create($setting);
            }
        }
    }

    /**
     * Returns array of settings.
     *
     * @return array[]
     */
    private function settingList(): array
    {
        return [
            [
                'key' => 'system.base.log.web_access',
                'name' => 'Log Web access / requests',
                'description' => 'Log GET, POST, PUT, DELETE that requested by browser',
                'value' => 1,
                'field' => [
                    'name' => 'value',
                    'label' => 'Log Web access / requests',
                    'type' => 'checkbox',
                ],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'filesystems.disks.s3.key',
                'name' => 'Amazon S3 Access Key',
                'description' => '',
                'value' => '',
                'field' => [
                    'name' => 'value',
                    'label' => 'Amazon S3 Access Key',
                    'type' => 'text',
                ],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'filesystems.disks.s3.secret',
                'name' => 'Amazon S3 Secret Key',
                'description' => '',
                'value' => '',
                'field' => [
                    'name' => 'value',
                    'label' => 'Amazon S3 Secret Key',
                    'type' => 'text',
                ],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'filesystems.disks.s3.region',
                'name' => 'Amazon S3 Region',
                'description' => '',
                'value' => '',
                'field' => [
                    'name' => 'value',
                    'label' => 'Amazon S3 Region',
                    'type' => 'text',
                ],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'filesystems.disks.s3.bucket',
                'name' => 'Amazon S3 Bucket Name',
                'description' => '',
                'value' => '',
                'field' => [
                    'name' => 'value',
                    'label' => 'Amazon S3 Bucket Name',
                    'type' => 'text',
                ],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'filesystems.disks.s3.url',
                'name' => 'Amazon S3 URL',
                'description' => '',
                'value' => '',
                'field' => [
                    'name' => 'value',
                    'label' => 'Amazon S3 URL',
                    'type' => 'text',
                ],
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
