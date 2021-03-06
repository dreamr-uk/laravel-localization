<?php namespace Mcamara\LaravelLocalization\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;

class LocaleCookieRedirect extends LaravelLocalizationMiddlewareBase
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
     public function handle($request, Closure $next) {
         // If the URL of the request is in exceptions.
         if ($this->shouldIgnore($request)) {
             return $next($request);
         }

         $params = explode('/', $request->path());
         $params[0] = substr($params[0], 0, 2);
         $locale = $request->cookie('locale', false);

         if (\count($params) > 0 && app('laravellocalization')->checkLocaleInSupportedLocales($params[0])) {
            return $next($request)->withCookie(cookie()->forever('locale', $params[0]));
         }

         if ($locale && app('laravellocalization')->checkLocaleInSupportedLocales($locale) && !(app('laravellocalization')->isHiddenDefault($locale))) {
           $redirection = app('laravellocalization')->getLocalizedURL($locale);
           $redirectResponse = new RedirectResponse($redirection, 302, ['Vary' => 'Accept-Language']);

           return $redirectResponse->withCookie(cookie()->forever('locale', $params[0]));
         }

         return $next($request);
     }
}
