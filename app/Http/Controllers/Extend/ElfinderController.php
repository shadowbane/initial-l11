<?php

namespace App\Http\Controllers\Extend;

use Barryvdh\Elfinder\ElfinderController as EC;
use Illuminate\Foundation\Application;

/**
 * Class ElfinderController.
 */
class ElfinderController extends EC
{
    /**
     * ElfinderController constructor.
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->middleware(function ($request, $next) {
            if (auth()->guest() || ! auth()->user()->hasRole('System Administrators')) {
                config([
                    'elfinder.disks' => [
                    ],
                ]);
            }

            return $next($request);
        });
        parent::__construct($app);
    }
}
