<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Permission;
use App\Models\Role;
use Exception;
use Illuminate\Database\Seeder;

class ExportMenuRolePermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $s = DIRECTORY_SEPARATOR;
        $path = database_path('base').$s;
        $menuFile = $path.'menu.json';
        $permissionFile = $path.'permission.json';
        $rolePermissionFile = $path.'role_permission.json';
        $statusFile = $path.'mst_status.json';

        $this->overwriteMenu($menuFile);
        $this->overwritePermission($permissionFile);
        $this->overwriteRole($rolePermissionFile);
    }

    /**
     * @param  $file
     */
    private function overwriteMenu($file): void
    {
        $json = MenuItem::get()->toJson(JSON_PRETTY_PRINT);
        $this->openAndWrite($file, $json);
        $this->command->info('MenuItem file replaced!');
    }

    /**
     * @param  $file
     */
    private function overwritePermission($file): void
    {
        $json = Permission::get()->toJson(JSON_PRETTY_PRINT);
        $this->openAndWrite($file, $json);
        $this->command->info('Permission file replaced!');
    }

    /**
     * @param  $file
     */
    private function overwriteRole($file): void
    {
        $json = Role::with('permissions', 'menuitems')->get()->toJson(JSON_PRETTY_PRINT);
        $this->openAndWrite($file, $json);
        $this->command->info('Role file replaced!');
    }

    /**
     * @param  $file
     * @param  $text_to_write
     */
    private function openAndWrite($file, $text_to_write): void
    {
        try {
            $openedFile = fopen($file, 'w') or exit('Cannot open '.$file.'!');
            fwrite($openedFile, $text_to_write);
            fclose($openedFile);
        } catch (Exception $exception) {
            exit($exception->getMessage());
        }
    }
}
