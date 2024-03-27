<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Exception;
use Jenssegers\Agent\Agent;
use Spatie\Activitylog\Models\Activity as ac;

class Activity extends ac
{
    use CrudTrait;

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
            } catch (Exception $e) {
                abort(500, $e->getMessage());
            }
        });
    }

    /**
     * Get User's request data.
     *
     * @return array
     */
    private static function getRequestData()
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
    private static function getBrowserDetail()
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
     * @return mixed|string|null
     */
    public function getCauser()
    {
        if ($this->causer instanceof User) {
            return $this->causer->name;
        }

        return null;

    }
}
