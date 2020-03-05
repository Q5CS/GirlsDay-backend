<?php

namespace App\Model;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'sex', 'mobile', 'qz5z_uid', 'qz5z_grade', 'qz5z_class', 'qz5z_number', 'token', 'refresh_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token', 'refresh_token', 'remember_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
//        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['is_female', 'is_graduated'];

    /**
     * 获取该用户发送的祝福
     */
    public function blessings()
    {
        return $this->hasMany('App\Model\Blessing', 'uid', 'id');
    }

    /**
     * 获取该用户许下的愿望
     */
    public function wishes()
    {
        return $this->hasMany('App\Model\Wish', 'uid', 'id');
    }

    /**
     * 获取该用户认领的愿望
     */
    public function assigned_wishes()
    {
        return $this->hasMany('App\Model\Wish', 'assigned_uid', 'id');
    }

    /**
     * 返回用户是否为女生
     *
     * @return boolean
     */
    public function getIsFemaleAttribute()
    {
        return $this->sex == '女';
    }

    /**
     * 返回用户是否已毕业
     *
     * @return boolean
     */
    public function getIsGraduatedAttribute()
    {
        $graduateYear = $this->getNumber($this->qz5z_grade) + 3;
        $graduateTime = strtotime("$graduateYear-6-8 17:00:00"); // 应毕业时间
        if ($graduateTime < time()) {
            // 应毕业时间比现在早，说明已经毕业了
            return true;
        }
        return false;
    }

    /**
     * 提取字符串中的数字
     *
     * @param $str
     * @return Integer
     */
    private function getNumber($str)
    {
        return intval(preg_replace('/\D/s', '', $str));
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
