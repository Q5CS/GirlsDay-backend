<?php

namespace App\Http\Controllers\Api;

use Auth;
use Storage;
use Illuminate\Http\Request;

class FileController extends Controller
{
    // 文件上传
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240',
        ]);

        $upYun = Storage::disk('upyun');
        $_path = 'girlsday_user_uploads'.DIRECTORY_SEPARATOR.date('Y-m-d',time()).DIRECTORY_SEPARATOR.$request->file->hashName();
        $upYun->write($_path, file_get_contents($request->file->getRealPath()));
        $path = $upYun->getUrl($_path);

        return ['code' => 1, 'message' => '上传成功', 'path' => $path];
    }
}
