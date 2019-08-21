<?php

namespace App\Http\Middleware;

use App\Classes\JwtAuth;
use Closure;

class ApiAuthMiddleware
{
    protected $jwtAuth;

    public function __construct ()
    {
        $this->jwtAuth = new JwtAuth();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $checkToken = $this->jwtAuth->checkToken($token);

        if ($checkToken){
            return $next($request);
        }else{
            $data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'NO Autorizado.'
            ];

            return response()->json($data,$data['code']);
        }

    }
}
