<?php

namespace Workbench\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class UseRequestHostForAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        $rootUrl = $request->getSchemeAndHttpHost();

        if (filled($rootUrl)) {
            config()->set('app.url', $rootUrl);
            URL::forceRootUrl($rootUrl);
        }

        return $next($request);
    }
}
