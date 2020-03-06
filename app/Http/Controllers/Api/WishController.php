<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\Wish;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WishController extends Controller
{
    /**
     * 获取当前统计数据和设置信息
     *
     * @return Response
     */
    public function stat()
    {
        $data = [
            'total_wishes' => Wish::count(),
            'unclaimed_wishes' => Wish::where(['status' => 100])->count(),
            'claimed_wishes' => Wish::where(['status' => 200])->count(),
            'completed_wishes' => Wish::where(['status' => 300])->count(),
            'max_wish_num' => (int)env('MAX_WISH_COUNT'),
            'max_claimed_and_in_progress_wishes' => (int)env('MAX_IN_PROGRESS_WISH_COUNT'),
        ];
        return response($data);
    }

    /**
     * 获取愿望列表
     *
     * @param Request $request
     * @return Response
     */
    public function getWishList(Request $request)
    {
        $data = $request->only(['limit', 'is_graduate']);

        $blessings = Wish::select(['id', 'status', 'uid', 'is_graduate', 'assigned_uid', 'content', 'type', 'file_json', 'created_at'])
            ->where('is_graduate', $data['is_graduate'] == 'true')
            ->where('status', 100)
            ->inRandomOrder()
            ->take($data['limit'])
            ->get();

        return response($blessings);
    }

    /**
     * 获取单个愿望
     *
     * @param Request $request
     * @return Response
     */
    public function getWish(Request $request)
    {
        $wish = Wish::select(['id', 'status', 'uid', 'qq', 'is_graduate', 'assigned_uid', 'content', 'type', 'file_json', 'assigned_at', 'completed_at', 'created_at'])
            ->find($request->id);

        return response($wish);
    }

    /**
     * 获取我发布的愿望
     *
     * @param Request $request
     * @return Response
     */
    public function getMyWishes(Request $request)
    {
        $wishes = Wish::select(['id', 'status', 'uid', 'qq', 'is_graduate', 'assigned_uid', 'content', 'type', 'file_json', 'assigned_at', 'completed_at', 'created_at'])
            ->where('uid', $request->user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response($wishes);
    }

    /**
     * 获取我认领的愿望
     *
     * @param Request $request
     * @return Response
     */
    public function getMyClaimedWishes(Request $request)
    {
        $wishes = Wish::select(['id', 'status', 'uid', 'qq', 'is_graduate', 'assigned_uid', 'content', 'type', 'file_json', 'assigned_at', 'completed_at', 'created_at'])
            ->where('assigned_uid', $request->user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response($wishes);
    }

    /**
     * 保存愿望
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveWish(Request $request)
    {
        $user = $request->user;

        if (!app()->isLocal() && !$user->is_female) {
            throw new HttpException(400, "宁也过女生节？");
        }

        if ($user->wishes->count() >= (int)env('MAX_WISH_COUNT')) {
            throw new HttpException(400, '心愿数已达到上限！');
        }

        $this->validate($request, [
            'qq' => 'string|required|max:20',
            'message' => 'string|required|max:140'
        ]);

        $MAX_COUNT = env('MAX_WISH_COUNT');
        $count = $request->user->wishes->count();
        if ($count >= $MAX_COUNT) {
            throw new HttpException(400, "最多发布 $MAX_COUNT 条愿望");
        }

        $data = [
            'uid' => $user->id,
            'qq' => $request->qq,
            'status' => 100, // 代表已发布未接单
            'type' => $request->type,
            'content' => $request->message,
            'file_json' => $request->file_json,
            'is_graduate' => $user->is_graduated,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ];

        Wish::create($data);

        Log::info('Wish added.', [
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '发布成功']);
    }

    /**
     * 删除愿望
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function deleteWish(Request $request)
    {
        $user = $request->user;
        $wish = Wish::find($request->id);

        if ($wish->uid != $user->id) {
            throw new HttpException(400, '你不能删除别人的愿望！');
        }

        $wish->delete();

        Log::info('Wish deleted.', [
            'id' => $request->id,
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '删除成功']);
    }

    /**
     * 修改愿望
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function editWish(Request $request)
    {
        $user = $request->user;
        $wish = Wish::find($request->id);

        if ($wish->uid != $user->id) {
            throw new HttpException(400, '你不能修改别人的愿望！');
        }

        if ($wish->status >= 200) {
            throw new HttpException(400, '已被认领的愿望无法再被修改！');
        }

        $data = [
            'qq' => $request->qq,
            'type' => $request->type,
            'content' => $request->message,
            'file_json' => $request->file_json,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ];

        $wish->update($data);

        Log::info('Wish edited.', [
            'id' => $request->id,
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '修改成功']);
    }

    /**
     * 认领愿望
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function claimWish(Request $request)
    {
        $user = $request->user;
        $wish = Wish::find($request->id);

        if ($wish->status != 100) {
            throw new HttpException(400, '只能认领未被认领的愿望！');
        }

        if ($wish->uid == $user->id) {
            throw new HttpException(400, '你不能认领自己的愿望！');
        }

        if ($user->assigned_wishes->where('status', '<', 300)->count() >= (int)env('MAX_IN_PROGRESS_WISH_COUNT')) {
            throw new HttpException(400, '当前可认领的愿望已达到上限！');
        }

        $data = [
            'status' => 200,
            'assigned_uid' => $user->id,
            'assigned_at' => now()
        ];

        $wish->update($data);

        Log::info('Wish claimed.', [
            'id' => $request->id,
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '认领成功']);
    }

    /**
     * 取消认领愿望
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unClaimWish(Request $request)
    {
        $user = $request->user;
        $wish = Wish::find($request->id);

        if ($wish->status != 200) {
            throw new HttpException(400, '只能取消认领已被认领的愿望！');
        }

        if ($wish->uid != $user->id && $wish->assigned_uid != $user->id) {
            throw new HttpException(400, '你不能取消认领与你无瓜愿望！');
        }

        $data = [
            'status' => 100,
            'assigned_uid' => null,
            'assigned_at' => null
        ];

        $wish->update($data);

        Log::info('Wish completed.', [
            'id' => $request->id,
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '取消认领成功']);
    }

    /**
     * 确认实现愿望
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function completeWish(Request $request)
    {
        $user = $request->user;
        $wish = Wish::find($request->id);

        if ($wish->status != 200) {
            throw new HttpException(400, '只能确认实现进行中的愿望！');
        }

//        if ($wish->uid != $user->id && $wish->assigned_uid != $user->id) {
//            throw new HttpException(400, '你不能确认实现与你无瓜愿望！');
//        }

        if ($wish->uid != $user->id) {
            throw new HttpException(400, '只有愿望发布者可以确认实现！');
        }

        $data = [
            'status' => 300,
            'completed_uid' => $user->id,
            'completed_at' => now()
        ];

        $wish->update($data);

        Log::info('Wish completed.', [
            'id' => $request->id,
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '确认实现成功']);
    }
}
