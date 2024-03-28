<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
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
    private function setuserMustChangePassword(): bool
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

    private function getCurrentActionGroup(string $method): string
    {
        return config('system.system.controllers.action_groups.'.$method);
    }

    private function getPermision(string $class, string $actionGroup): string
    {
        return $class.'@'.$actionGroup;
    }
}
