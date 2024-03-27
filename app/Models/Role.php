<?php

namespace App\Models;

use App\Models\Traits\LogsActivity;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends \Spatie\Permission\Models\Role
{
    use CrudTrait;
    use LogsActivity;
    use \Illuminate\Database\Eloquent\Concerns\HasTimestamps, \App\Models\Traits\CustomTimestampsTrait {
        \App\Models\Traits\CustomTimestampsTrait::freshTimestamp insteadof \Illuminate\Database\Eloquent\Concerns\HasTimestamps;
    }

    protected $connection = 'mysql';
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'guard_name', 'updated_at', 'created_at'];
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function menuitems(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\MenuItem', 'menu_item_role', 'role_id', 'menu_item_id');
    }
}
