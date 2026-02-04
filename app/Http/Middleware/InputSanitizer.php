<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InputSanitizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$input) {
            // Strip tags but allow some basic formatting if needed (can be adjusted)
            // For now, strip EVERYTHING strictly for security.
            if (is_string($input)) {
                 $input = strip_tags($input);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
