<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Symfony\Component\HttpFoundation\JsonResponse;

class JpJsonResponse
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
        $response = $next($request);
        
        //JSONでない場合はそのまま
        if (!$response instanceof JsonResponse) {
            return $response;
        }
 
        // Unicodeエスケープさせないようにオプションを追加
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_UNESCAPED_UNICODE);
 
        return $response;
    }
}