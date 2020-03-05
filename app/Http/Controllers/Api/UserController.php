<?php

namespace App\Http\Controllers\Api;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends Controller
{
    /**
     * 处理用户登出逻辑
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::logout();
        return response(['message' => '退出成功']);
    }

    /**
     * 获取个人信息
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $user = $request->user;

        $data = $user->toArray();
        $data['avatar'] = 'https://i.loli.net/2019/11/30/QPkvLqHFXrJepmo.png';
//        $data['role'] = $this->getRoleObj($data['roleID']);
        // var_dump($data);
        return response($data);
    }
}
