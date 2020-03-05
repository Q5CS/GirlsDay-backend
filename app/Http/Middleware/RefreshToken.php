<?php

namespace App\Http\Middleware;

use App\Model\User;
use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class RefreshToken extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws JWTException
     */
    public function handle($request, Closure $next)
    {
        $token = null;
        //检查请求中是否带有token 如果没有token值则抛出异常
        $this->checkForToken($request);

        $model = null;
        $id = null;

        try {
            if (!$this->auth->parseToken()) {
                throw new UnauthorizedHttpException('jwt-auth', '未登录');
            }
            $model = auth()->payload()['model'];
            $id = auth()->payload()['sub'];
        } catch (TokenExpiredException $exception) {
            try {
                //首先获得过期token 接着刷新token
                $token = JWTAuth::refresh(JWTAuth::getToken());
                auth()->setToken($token);
                $model = auth()->payload()['model'];
                $id = auth()->payload()['sub'];
            } catch (JWTException $exception) {
                throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
            }
        }

        if (!$model || !$id) {
            throw new UnauthorizedHttpException('jwt-auth', 'token 错误');
        }

//        switch ($model) {
//            case 'user':
//                $request->user = User::find($id);
//                break;
//            default:
//                throw new UnauthorizedHttpException('jwt-auth', '用户模型错误');
//        }

        $request->user = auth()->user();

        if (is_null($token)) {
            return $next($request);
        } else {
            JWTAuth::setToken($token);
            $request->headers->set('Authorization', 'Bearer ' . $token); // token被刷新之后，保证本次请求在controller中需要根据token调取登录用户信息能够执行成功
            //将token值返回到请求头
            return $this->setAuthenticationHeader($next($request), $token);
        }
    }
}
