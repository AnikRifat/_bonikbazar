<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return \Illuminate\Http\RedirectResponse|JsonResponse
     */
    public function handle(Request $request, Closure $next) {
//        echo $request->getRequestUri();
        $exclude_uri = array(
            '/user-signup',
            '/api/user-signup',
            '/logout',
            '/api/manage-favourite'
        );
        $excludeEmails = [
            'demooff@gmail.com'
        ];
        if (config('app.demo_mode') && !$request->isMethod('get') && Auth::user() && !in_array(Auth::user()->email, $excludeEmails, true) && !in_array($request->getRequestUri(), $exclude_uri)) {
            if ($request->is('api/*') || $request->ajax()) {
                return response()->json(array(
                    'error'   => true,
                    'message' => "This is not allowed in the Demo Version.",
                    'code'    => 112
                ));
            }

            return redirect()->back()->withErrors([
                'message' => "This is not allowed in the Demo Version"
            ]);
        }
        return $next($request);
    }
}
