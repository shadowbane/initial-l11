<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MakePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:permission {namespace} {shortname} {--guard=} {--role=*} {--description=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Permission and assign to role';

    private array|Collection|null $roles;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data['name'] = $this->argument('namespace');
        $data['shortname'] = $this->argument('shortname');
        $data['guard_name'] = config('auth.defaults.guard');
        $data['description'] = null;
        $this->roles = [];

        if (! is_null($this->option('guard'))) {
            $data['guard_name'] = $this->option('guard');
        }

        if ($this->option('role') != null) {
            $this->roles = $this->option('role');
        }

        if ($this->option('description') != null) {
            $data['description'] = $this->option('description');
        }

        $roles = collect();

        try {
            DB::beginTransaction();
            foreach ($this->roles as $roleName) {
                $role = Role::whereName($roleName)->first();
                if (! $role instanceof Role) {
                    throw new \RuntimeException("Role named '{$roleName}' not found");
                }

                $roles->add($role);
            }

            $service = new PermissionService();
            $service->createPermission(
                classString: $data['name'],
                shortName: $data['shortname'],
                roles: $roles,
                guard: $data['guard_name'],
                description: $data['description'],
            );

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            $this->error("Error: {$exception->getMessage()}");

            return;
        }
    }
}
