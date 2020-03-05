<?php

namespace App\Http\Controllers\Api\User;

use App\Model\User;
use Dingo\Api\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('refresh', ['except' => ['login']]);
    }

    /**
     * 通过五中开放授权平台的 code 进行 Oauth 登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        // 验证规则
        $params = $this->validate($request, [
            "code" => "required"
        ]);

        // code 换 token
        $http = new Client;
        try {
            $response = $http->post(env('QZ5Z_URL'), [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => env('QZ5Z_APP_ID'),
                    'client_secret' => env('QZ5Z_APP_KEY'),
                    'redirect_uri' => env('QZ5Z_REDIRECT_URL'),
                    'code' => $params['code'],
                ],
            ]);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            $response = json_decode($e->getResponse()->getBody());
            $err = $response->hint;
            throw new HttpException(400,'获取 token 失败：'.$err);
        } catch (\Exception $e) {
            throw new HttpException(400,'获取 token 失败：未知错误');
        }

        $response = json_decode($response->getBody());

        // 判断是否有错误
        if(property_exists($response, 'error')) {
            throw new HttpException(400,'获取 token 失败：'.$response->error_description);
        }

        $token = $response->access_token;
        $refresh_token = $response->refresh_token;

        // token 换信息
        try {
            $response = $http->post(env('QZ5Z_GET_USER_URL'), [
                'form_params' => [
                    'access_token' => $token,
                    'scope' => 'phone',
                ],
            ]);
        } catch (RequestException $e) {
            // Catch all 4XX errors
            throw new HttpException(400,'获取用户信息失败：token 错误');
        } catch (\Exception $e) {
            throw new HttpException(500,'获取用户信息失败：未知错误');
        }
        $response = json_decode($response->getBody());
        $userData = $response;
        // 把获取到的用户信息存入数据库
        $user = User::where('qz5z_uid', $userData->uid)->first();
        $t = [
            'name' => $userData->name,
            'sex' => $userData->sex,
            'mobile' => $userData->phone,
            'qz5z_uid' => $userData->uid,
            'qz5z_grade' => $userData->grade,
            'qz5z_class' => $userData->class,
            'qz5z_number' => $userData->number,
            'token' => $token,
            'refresh_token' => $refresh_token
        ];
        if(!empty($user)) {
            $user->update($t);
        } else {
            User::create($t);
        }

        // 使用 Auth 登录用户，如果登录成功，则返回 201 的 code 和 token，如果登录失败则返回
        $user = User::where('qz5z_uid', $userData->uid)->first();
        $token = Auth::guard('api')->claims(['model' => 'user'])->login($user);
        if ($token) {
            Log::info('Student user login.', [
                'id' => $user->id,
                'ip' => $request->ip(),
                'ua' => $request->userAgent()
            ]);
            return response(['token' => 'bearer ' . $token], 201);
        } else {
            Log::notice('Student user failed to login.', [
                'id' => $user->id,
                'ip' => $request->ip(),
                'ua' => $request->userAgent()
            ]);
            throw new HttpException(400,'Oauth 登录错误');
        }
    }

}
