<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blessing extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'title', 'content', 'ip', 'ua',
    ];

    protected $hidden = [
        'user'
    ];

    protected $appends = ['user_info'];

    /**
     * 返回对应用户信息
     *
     * @return array
     */
    public function getUserInfoAttribute()
    {
        return [
            'name' => $this->user->name,
            'sex' => $this->user->sex,
        ];
    }

    /**
     * 获取该评论所对应的用户
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User', 'uid');
    }
}
