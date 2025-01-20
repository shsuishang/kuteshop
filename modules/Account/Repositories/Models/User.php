<?php
// +----------------------------------------------------------------------
// | ShopSuite商城系统 [ 赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | 版权所有 随商信息技术（上海）有限公司
// +----------------------------------------------------------------------
// | 未获商业授权前，不得将本软件用于商业用途。禁止整体或任何部分基础上以发展任何派生版本、
// | 修改版本或第三方版本用于重新分发。
// +----------------------------------------------------------------------
// | 官方网站: https://www.kuteshop.cn  https://www.kuteshop.cn
// +----------------------------------------------------------------------
// | 版权和免责声明:
// | 本公司对该软件产品拥有知识产权（包括但不限于商标权、专利权、著作权、商业秘密等）
// | 均受到相关法律法规的保护，任何个人、组织和单位不得在未经本团队书面授权的情况下对所授权
// | 软件框架产品本身申请相关的知识产权，禁止用于任何违法、侵害他人合法权益等恶意的行为，禁
// | 止用于任何违反我国法律法规的一切项目研发，任何个人、组织和单位用于项目研发而产生的任何
// | 意外、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其造成的损失 (包括但不限于直接、间接、
// | 附带或衍生的损失等)，本团队不承担任何法律责任，本软件框架只能用于公司和个人内部的
// | 法律所允许的合法合规的软件产品研发，详细见https://www.modulithshop.cn/policy
// +----------------------------------------------------------------------


namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Modules\Admin\Repositories\Models\UserAdmin;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User.
 *
 * @package namespace App\Repositories\Models;
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    protected $table = 'account_user_base';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_password'
    ];

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
        return [
            'user_id' => $this->user_id,
            'user_account' => $this->user_account,
            'user_salt' => $this->user_salt
        ];
    }

    /**
     * @return array 返回用户密码 和 user_salt
     */
    public function getAuthPassword()
    {
        // TODO: Implement getAuthPassword() method.
        return [$this->user_password, $this->user_salt];
    }


    /**
     * 获取用户ID
     * @return mixed
     */
    public static function getUserId()
    {
        return auth()->id() ?? 0;
    }


    /**
     * 关联 user_info 表
     */
    public function info()
    {
        return $this->hasOne(UserInfo::class, 'user_id', 'user_id');
    }


    /**
     * 关联 user_admin 表
     */
    public function admin()
    {
        return $this->hasOne(UserAdmin::class, 'user_id', 'user_id');
    }


    /**
     * 获取用户信息
     * @return AuthenticatableContract|null
     */
    public static function getUser()
    {
        $user = auth()->user();

        if ($user) {
            $user->load(['info', 'admin']);

            // 获取昵称
            $user->user_nickname = $user->info->user_nickname ?? null;
            // 用户头像
            $user->user_avatar = $user->info->user_avatar ?? null;
        }

        return $user;
    }


    /**
     * 获取是否是超级管理员属性
     *
     * @return bool
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->admin && $this->admin->user_is_superadmin;
    }


    /**
     * 获取是否是管理员属性
     *
     * @return bool
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->admin !== null;
    }


    /**
     * 判断用户是否是超级管理员
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }


    /**
     * 判断用户是否是超级管理员
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }


    /**
     * 用户角色权限ID
     *
     * @return bool
     */
    public function userRoleId(): int
    {
        return $this->admin->user_role_id;
    }

}
