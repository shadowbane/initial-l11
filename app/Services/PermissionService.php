<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class PermissionService
{
    /**
     * @return bool
     */
    public function check(): bool
    {
        $this->redirectIfGuest();

        // If the accessed page is in allowed url list,
        // then no need to check further.
        if (in_array($this->getLastUrl(request()), $this->allowedUrls())) {
            return true;
        }

        [$class, $method] = $this->getCurrentClassAndMethod();
        $actionGroup = $this->getCurrentActionGroup($method);

        if (auth()->user()->can($this->getPermision($class, $actionGroup))) {
            return true;
        }

        return false;
    }

    /**
     * @return RedirectResponse|null
     */
    public function redirectIfGuest(): ?RedirectResponse
    {
        if (auth()->guest()) {
            return redirect('/login');
        }

        return null;
    }

    /**
     * Check if authenticated user must change their password.
     *
     * @return void
     */
    private function checkMustChangePassword(): void
    {
        // must be logged in!
        if (bacpack_auth()->user() && bacpack_auth()->user()->must_change_password) {
            abort(redirect()->route('auth.change_password'));
        }
    }

    /**
     * Get absolute last path of the URL.
     *
     * @param  $request
     *
     * @return mixed
     */
    private function getLastUrl($request): mixed
    {
        return last(explode('/', $request->url()));
    }

    /**
     * Bypassed URL on middleware.
     *
     * @return array
     */
    private function allowedUrls(): array
    {
        return [
            'register',
            'login',
            'logout',
            'dashboard',
            'lang',
            'stop-impersonating',
            'user-token',
            last(explode('/', config('app.url'))),
        ];
    }

    /**
     * Set 'must_change_password' on logged in user to true.
     *
     * @return bool
     */
    private function setUserMustChangePassword(): bool
    {
        return auth()->user()->update([
            'must_change_password' => true,
        ]);
    }

    /**
     * @return void
     */
    public function addLastLogin(): void
    {
        if (auth()->user()) {
            activity()->withoutLogs(function () {
                User::withoutEvents(function () {
                    return auth()->user()->update([
                        'last_login_ip' => request()->getClientIp(),
                        'last_login' => now()->toDateTimeString(),
                    ]);
                });
            });

            $this->createLoginActivityLog();
        }
    }

    /**
     * @param  string  $classString
     * @param  string  $shortName
     * @param  Collection  $roles
     * @param  string  $guard
     * @param  string|null  $description
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function createPermission(
        string $classString,
        string $shortName,
        Collection $roles,
        string $guard = 'web',
        ?string $description = null,
    ): void {
        if (count(config('system.system.controllers.valid_actions')) < 1) {
            throw new \RuntimeException('No valid_actions set');
        }

        // Create permission
        // Here, we append the $classString with valid action from config
        foreach (config('system.system.controllers.valid_actions') as $action) {
            $newPermission['name'] = "{$classString}@{$action}";
            $newPermission['shortname'] = $shortName;
            $newPermission['guard'] = $guard;
            $newPermission['description'] = $description;

            $permissions[] = Permission::create($newPermission);
        }

        // Loop role, and assign newly created permission
        /** @var Role $role */
        foreach ($roles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Store login log on activity log table.
     *
     * @return void
     */
    private function createLoginActivityLog(): void
    {
        activity('access')
            ->log('login')
            ->causedBy(auth()->user());
    }

    /**
     * Dissect accessed class and method by current route.
     *
     * @return array
     */
    private function getCurrentClassAndMethod(): array
    {
        [$class, $method] = explode('@', Route::currentRouteAction());

        return [$class, $method];
    }

    /**
     * Get the current action group from config array.
     *
     * @param  string  $method
     *
     * @return string
     */
    private function getCurrentActionGroup(string $method): string
    {
        return config('system.system.controllers.action_groups.'.$method);
    }

    /**
     * Get the permission string from class and action group.
     *
     * @param  string  $class
     * @param  string  $actionGroup
     *
     * @return string
     */
    private function getPermision(string $class, string $actionGroup): string
    {
        return $class.'@'.$actionGroup;
    }
}
