<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Spatie\Activitylog\Models\Activity as ac;

class Activity extends ac
{
    use CrudTrait;
    use \Illuminate\Database\Eloquent\Concerns\HasTimestamps, \App\Models\Traits\CustomTimestampsTrait {
        \App\Models\Traits\CustomTimestampsTrait::freshTimestamp insteadof \Illuminate\Database\Eloquent\Concerns\HasTimestamps;
    }

    protected $casts = [
        'browser_detail' => 'collection',
        'request_detail' => 'collection',
        'properties' => 'collection',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            try {
                $model->ip = request()->ip();
                $model->browser_detail = self::getBrowserDetail();
                $model->request_detail = self::getRequestData();
                if (! app()->runningInConsole()) {
                    $model->request_identifier = request()->identifier();
                }
            } catch (\Throwable $e) {
                Log::error('Error when creating activity log: '.$e->getMessage(), [
                    'exception' => $e,
                    'previous' => $e->getPrevious(),
                    'trace' => $e->getTrace(),
                ]);
                abort(500, $e->getMessage());
            }
        });
    }

    /**
     * Get User's request data.
     *
     * @return array
     */
    private static function getRequestData(): array
    {
        $req['ajax'] = request()->ajax();
        $req['isJson'] = request()->isJson();
        $req['wantsJson'] = request()->wantsJson();
        $req['method'] = request()->method();
        $req['secure'] = request()->secure();
        $req['url'] = request()->url();
        $req['path'] = request()->path();
        $req['query'] = request()->query();

        return $req;
    }

    /**
     * Get user's browser.
     *
     * @return array
     */
    private static function getBrowserDetail(): array
    {
        $agent = new Agent();

        return [
            'isMobile' => $agent->isMobile(),
            'isTablet' => $agent->isTablet(),
            'isDesktop' => $agent->isDesktop(),
            'isBot' => $agent->isRobot(),
            'browserFamily' => $agent->browser(),
            'browserVersion' => $agent->version($agent->browser()),
            'osFamily' => $agent->platform(),
            'osVersion' => $agent->version($agent->platform()),
            'deviceFamily' => $agent->device(),
            'deviceVersion' => $agent->version($agent->device()),
        ];
    }

    /**
     * @return string|null
     */
    public function getCauser(): ?string
    {
        if ($this->causer instanceof User) {
            return $this->causer->name;
        }

        return null;
    }
}
