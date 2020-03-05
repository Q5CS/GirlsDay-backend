<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wish extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid', 'qq', 'status', 'type', 'content', 'file_json', 'is_graduate', 'assigned_uid', 'assigned_at', 'completed_at', 'ip', 'ua',
    ];

    protected $hidden = [
        'user', 'assigned_user'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_graduate' => 'boolean',
        'file_json' => 'object',
    ];

    protected $appends = ['user_info', 'assigned_user_info'];


    /**
     * 返回对应用户信息
     *
     * @return array
     */
    public function getUserInfoAttribute()
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'sex' => $this->user->sex,
            'qz5z_grade' => $this->user->qz5z_grade,
            'qz5z_class' => $this->user->qz5z_class,
        ];
    }

    /**
     * 返回对应用户信息
     *
     * @return array
     */
    public function getAssignedUserInfoAttribute()
    {
        return is_null($this->assigned_uid) ? null : [
            'id' => $this->user->id,
            'name' => $this->assigned_user->name,
            'sex' => $this->assigned_user->sex,
            'qz5z_grade' => $this->assigned_user->qz5z_grade,
            'qz5z_class' => $this->assigned_user->qz5z_class,
        ];
    }

    /**
     * 获取该评论所对应的用户
     */
    public function user()
    {
        return $this->belongsTo('App\Model\User', 'uid');
    }

    /**
     * 获取该评论所对应的用户
     */
    public function assigned_user()
    {
        return $this->belongsTo('App\Model\User', 'assigned_uid');
    }
}
