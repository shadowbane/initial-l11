<?php

namespace App\Models;

use App\Models\Traits\LogsActivity;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Exception;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    use Cachable;
    use CrudTrait;
    use LogsActivity;
    use \Illuminate\Database\Eloquent\Concerns\HasTimestamps, \App\Models\Traits\CustomTimestampsTrait {
        \App\Models\Traits\CustomTimestampsTrait::freshTimestamp insteadof \Illuminate\Database\Eloquent\Concerns\HasTimestamps;
    }

    protected $connection = 'mysql';
    protected $table = 'menu_items';
    protected $fillable = ['name', 'grouping', 'type', 'link', 'page_id', 'parent_id', 'icon', 'admin_route'];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $hidden = ['created_at', 'updated_at'];

    public function getLogNameToUse(string $eventName = ''): string
    {
        return 'Model';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\MenuItem', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany('App\Models\MenuItem', 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Role', 'menu_item_role', 'menu_item_id', 'role_id');
    }

    /**
     * Get all menu items, in a hierarchical collection.
     * Only supports 2 levels of indentation.
     */
    public static function getTree()
    {
        $menu = self::orderBy('lft')->get();

        if ($menu->count()) {
            foreach ($menu as $k => $menu_item) {
                $menu_item->children = collect([]);

                foreach ($menu as $i => $menu_subitem) {
                    if ($menu_subitem->parent_id == $menu_item->id) {
                        $menu_item->children->push($menu_subitem);

                        // remove the subitem for the first level
                        $menu = $menu->reject(function ($item) use ($menu_subitem) {
                            return $item->id == $menu_subitem->id;
                        });
                    }
                }
            }
        }

        return $menu;
    }

    /**
     * @throws Exception
     */
    public static function getParent(): Collection
    {
        return self::all()->sortBy('name')->pluck('name', 'id');
    }
}
