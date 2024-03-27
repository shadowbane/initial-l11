<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use JsonException;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws JsonException
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('cache:clear');
        $user_role_permission = $this->getUserRolePermission();

        // Disable foreign key checks to make your life easier :)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate pivot table first to remove references
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('menu_item_role')->truncate();

        // Then, truncate the main table with data
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        // seed permissions
        $path = database_path('base'.DIRECTORY_SEPARATOR.'permission.json');
        if (file_exists($path)) {
            $permissions = json_decode(file_get_contents($path), true);

            foreach ($permissions as $permission) {
                $perm = Permission::insert($permission);
            }
        }

        // seed roles & it's permissions
        $path = database_path('base'.DIRECTORY_SEPARATOR.'role_permission.json');
        if (file_exists($path)) {
            $roles = json_decode(file_get_contents($path), true);

            foreach ($roles as $role) {
                $r = Role::firstOrCreate([
                    'name' => $role['name'],
                ]);

                $permission = collect($role['permissions'])->pluck('id');
                $menu = collect($role['menuitems'])->pluck('id');

                $r->permissions()->sync($permission);

                $r->menuitems()->sync($menu);
            }
        }

        // restore role & permission
        $this->restoreUserRolePermission($user_role_permission);

        // Reenable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Artisan::call('cache:clear');
        Artisan::call('config:clear');
    }

    /**
     * Get User's Roles and Permissions.
     */
    private function getUserRolePermission(): array
    {
        $users = User::all();
        $data = [];
        foreach ($users as $user) {
            $data[$user->id] = [
                'roles' => $user->roles()->pluck('id'),
                'permissions' => $user->permissions()->pluck('id'),
            ];
        }

        return $data;
    }

    /**
     * Restore User's Roles and Permissions.
     *
     * @param  array  $data
     */
    private function restoreUserRolePermission(array $data)
    {
        foreach ($data as $key => $val) {
            $user = User::find($key);

            // restore roles
            $user->roles()->sync($val['roles']);

            // restore permissions
            $user->permissions()->sync($val['permissions']);
        }
    }
}
