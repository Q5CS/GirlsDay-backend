<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Blessing;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BlessingController extends Controller
{
    /**
     * 获取祝福
     *
     * @param Request $request
     * @return Response
     */
    public function getBlessings(Request $request)
    {
        $data = $request->only(['limit', 'page']);

        $blessings = Blessing::select(['id', 'uid', 'title', 'content', 'created_at'])
            ->orderBy('id', 'desc')
            ->paginate($data['limit']);

        return response($blessings);
    }

    /**
     * 获取祝福数量
     *
     * @param Request $request
     * @return Response
     */
    public function countBlessings(Request $request)
    {
        $query = Blessing::count();
        return response(['count' => $query]);
    }

    /**
     * 保存祝福
     *
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function saveBlessing(Request $request)
    {
        $user = $request->user;

        $this->validate($request, [
            'message' => 'string|required|max:140'
        ]);

        $data = [
            'uid' => $user->id,
            'content' => $request->message,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ];

        Blessing::create($data);

        Log::info('Blessing added.', [
            'user_id' => $request->user->id,
            'ip' => $request->ip(),
            'ua' => $request->userAgent()
        ]);

        return response(['message' => '发布成功']);
    }
}
