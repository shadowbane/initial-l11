<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class MenuService
{
    public string $cacheTag;

    /**
     * MenuService constructor.
     *
     * @param  int  $cacheLifetime
     */
    public function __construct(
        protected int $cacheLifetime = 3600,
    ) {
        $this->cacheTag = 'menuTree-'.config('cache.prefix');
    }

    /**
     * Returns cache name.
     * This cache is per-user basis, based on their ID.
     *
     * @return string
     */
    private function getCacheName(): string
    {
        $cachePrefix = '-menuTree';

        return (
            auth()->guest()
                ? 'guest'
                : auth()->user()->getAuthIdentifier()
        ).$cachePrefix;
    }

    /**
     * Returns cached collection of menu items.
     * If the cache is not available, it will be generated and cached.
     */
    public function tree(): Collection
    {
        if (! cache()->tags([$this->cacheTag])->has($this->getCacheName())) {
            cache()
                ->tags([$this->cacheTag])
                ->put($this->getCacheName(), $this->getTree(), $this->cacheLifetime);
        }

        return cache()->tags([$this->cacheTag])->get($this->getCacheName());
    }

    /**
     * Returns the menu tree from database.
     */
    public function getTree(): Collection
    {
        $trees = collect();

        if (auth()->guest()) {
            return $trees;
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $roles = $user->roles->pluck('id')->toArray();

        $menuData = $this->runQuery($roles);

        // convert array to collection
        if (is_array($menuData)) {
            $menuData = collect($menuData);
        }

        $trees = $trees->merge($menuData->whereNull('parent_id'))->sortBy('lft');

        $menuData = $this->updateCollection($trees, $menuData);

        // prepare the menu items that have no parent
        foreach ($menuData as &$item) {
            $item->fullname = $item->name;
            $item->name = last(explode(' > ', $item->name));
            $item->childrens = $this->setChildren(item: $item, menuData: $menuData);
        }

        // loop again. this current loop will removes the childrens left from the menuData
        // and only append the parent items
        foreach ($menuData as &$item) {
            $trees->push($item);

            $menuData = $menuData->reject(function ($value) use ($item) {
                return $value->id === $item->id;
            });
        }

        // this should show empty array. If not, something is wrong!
        //        dd($menuData->toArray());

        return $trees;
    }

    /**
     * Get the query for the menu items model.
     *
     * @param  array  $roles
     * @return string
     */
    private function getQuery(array $roles): string
    {
        $roles = implode(',', $roles);

        return "
            WITH RECURSIVE menu_tree (`id`, `name`, `grouping`, `link`, `parent_id`, `lft`, `icon`, `role_id`) AS (
                SELECT `mi`.`id`,
                       `mi`.`name`,
                       `mi`.`grouping`,
                       `mi`.`link`,
                       `mi`.`parent_id`,
                       `mi`.`lft`,
                       `mi`.`icon`,
                       `mir`.`role_id`
                FROM menu_items mi
                         LEFT JOIN menu_item_role mir on mi.id = mir.menu_item_id
                WHERE `mi`.`parent_id` IS NULL
                  AND `mi`.`deleted_at` IS NULL

                UNION ALL

                SELECT `mi`.`id`,
                       CONCAT(mt.name, ' > ', mi.name) as `name`,
                       `mi`.`grouping`,
                       `mi`.`link`,
                       `mi`.`parent_id`,
                       `mi`.`lft`,
                       `mi`.`icon`,
                       `mir`.`role_id`
                FROM menu_tree mt
                         JOIN menu_items mi ON mt.id = mi.parent_id
                         LEFT JOIN menu_item_role mir on mi.id = mir.menu_item_id
                WHERE mi.deleted_at IS NULL
            )

            SELECT
                DISTINCT `id`, `name`, `grouping`, `link`, `parent_id`, `lft`, `icon`
            FROM
                menu_tree
            WHERE
                `role_id` IN ({$roles})
            ORDER BY `lft` ASC
        ";
    }

    /**
     * Get the records for given roles.
     *
     * @param  array  $roles
     * @return array
     */
    private function runQuery(array $roles): array
    {
        return DB::connection('mysql')->select(
            $this->getQuery($roles)
        );
    }

    /**
     * @param  stdClass  $item
     * @param  Collection  $menuData
     * @return Collection
     */
    private function setChildren(stdClass $item, Collection &$menuData): Collection
    {
        $childrens = $menuData->where('parent_id', $item->id)->sortBy('lft');
        $menuData = $this->updateCollection($childrens, $menuData);

        return $childrens;
    }

    /**
     * Deletes all menu cache.
     *
     * @return void
     */
    public function deleteAllCache(): void
    {
        cache()->tags([$this->cacheTag])->flush();
    }

    /**
     * Deletes logged in user cache.
     *
     * @return void
     */
    public function deleteUserCache(): void
    {
        cache()->tags([$this->cacheTag])->forget($this->getCacheName());
    }

    /**
     * @param  Collection  $trees
     * @param  array|Collection  $menuData
     * @return array|Collection
     */
    private function updateCollection(Collection $trees, array|Collection $menuData): array|Collection
    {
        $trees->each(function ($item) use (&$menuData) {
            $menuData = $menuData->reject(function ($value) use ($item) {
                return $value->id === $item->id;
            });

            $item->fullname = $item->name;
            $item->name = last(explode(' > ', $item->name));
            $item->childrens = $this->setChildren(item: $item, menuData: $menuData);
        });

        return $menuData;
    }
}
