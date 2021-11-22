<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\SystemConfiguration;

class CheckAppVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $sysconf = SystemConfiguration::where('CODE', 'APP-VERSION')->first(['value']);
        if(in_array($request->path(), ["api/unit/playback_view", "api/rencana_kerja/playback_view", "api/rencana_kerja/map_view"])) {
            return $next($request);
        }
        $app_version = $request->header('APP-VERSION', '');
        if($app_version!=$sysconf->value){
            return response()->json([
                'status'    => false, 
                'message'   => 'Silahkan Install Versi Terbaru !!', 
                'data'      => null
            ]);
        }
        return $next($request);
    }
}
