<?php

namespace App\Http\Controllers\Auth;

use App\Services\PermissionService;
use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BPLoginController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginController extends BPLoginController
{
    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $request->session()->regenerate();
        (new PermissionService())->addLastLogin();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect()->intended($this->redirectPath());
    }
}
