<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictIP
{
    /**
     * List of allowed IPs.
     *
     * @var array
     */
    protected $allowedIPs = [
        '111.125.194.83',
        '192.168.0.133',
        '192.168.0.115',
        '192.168.0.143',
        '1172.20.10.7',
        '192.168.0.135',
        '192.168.0.129',
        '192.168.0.112',
        // '111.125.194.83',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // if (!in_array($request->ip(), $this->allowedIPs)) {
        //     return response()->json(['error' => 'Unauthorized Access'], 403);
        // }

        return $next($request);
    }
}
