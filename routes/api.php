<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api'
], function ($api) {
    // 默认可访问
    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' => config('api.rate_limits.sign.expires'),
    ], function ($api) {
        // 在校生 Oauth 登录
        $api->post('student/session', 'User\AuthController@login')
            ->name('api.auth.student.login');
        // 平台相关数据
        $api->get('stat', 'WishController@stat')
            ->name('api.stat');
    });

    // 需要 token 验证的接口
    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.access.limit'),
        'expires' => config('api.rate_limits.access.expires'),
    ], function ($api) {
        $api->group(['middleware' => 'refresh'], function ($api) {
            // 用户注销
            $api->delete('session', 'UserController@logout')
                ->name('api.auth.logout');
            // 当前登录用户信息
            $api->get('session', 'UserController@me')
                ->name('api.auth.show');
            // 图片上传
            $api->post('/upload/image', 'FileController@uploadImage')
                ->name('api.upload.image');

            // 获取祝福数量
            $api->get('blessing/count', 'BlessingController@countBlessings')
                ->name('api.blessing.count');
            // 获取祝福
            $api->get('blessing', 'BlessingController@getBlessings')
                ->name('api.blessing.get');
            // 发表祝福
            $api->post('blessing', 'BlessingController@saveBlessing')
                ->name('api.blessing.save');

            // 获取愿望列表
            $api->get('wish', 'WishController@getWishList')
                ->name('api.wish.getList');
            // 获取单个愿望
            $api->get('wish/{id}', 'WishController@getWish')
                ->name('api.wish.get');
            // 获取我发布的愿望列表
            $api->get('myWish', 'WishController@getMyWishes')
                ->name('api.wish.getMyWishes');
            // 获取我领取的愿望列表
            $api->get('myClaimedWish', 'WishController@getMyClaimedWishes')
                ->name('api.wish.getMyClaimedWishes');
            // 发表愿望
            $api->post('wish', 'WishController@saveWish')
                ->name('api.wish.save');
            // 删除愿望
            $api->delete('wish/{id}', 'WishController@deleteWish')
                ->name('api.wish.delete');
            // 修改愿望
            $api->put('wish/{id}', 'WishController@editWish')
                ->name('api.wish.edit');
            // 认领愿望
            $api->post('wish/{id}/claim', 'WishController@claimWish')
                ->name('api.wish.claim');
            // 取消认领愿望
            $api->delete('wish/{id}/claim', 'WishController@unClaimWish')
                ->name('api.wish.unClaim');
            // 确认愿望实现
            $api->post('wish/{id}/complete', 'WishController@completeWish')
                ->name('api.wish.complete');
        });
    });
});
