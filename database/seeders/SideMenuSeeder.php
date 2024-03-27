<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use JsonException;

class SideMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @throws JsonException
     */
    public function run(): void
    {
        $path = database_path('base'.DIRECTORY_SEPARATOR.'menu.json');
        if (file_exists($path)) {
            $menus = json_decode(file_get_contents($path), true);

            foreach ($menus as $menu) {
                MenuItem::updateOrCreate(['id' => $menu['id'], 'name' => $menu['name']], $menu);
            }
        }
    }
}
