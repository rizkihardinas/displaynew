<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class SettingPusherKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $setting = Setting::first();

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => $setting->pusher_key,
            'broadcasting.connections.pusher.secret' => $setting->pusher_secret,
            'broadcasting.connections.pusher.app_id' => $setting->pusher_app_id,
        ]);
        return $next($request);
    }
}
