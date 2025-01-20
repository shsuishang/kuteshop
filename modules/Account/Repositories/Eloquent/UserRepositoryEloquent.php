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


namespace Modules\Account\Repositories\Eloquent;

use Illuminate\Support\Facades\Hash;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserRepository;
use Modules\Account\Repositories\Models\User;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace Modules\Account\Repositories\Eloquent;
 */
class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * 设置用户密码
     * @param $user_id
     * @param $user_password
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection|mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function setUserPassword($user_id, $user_password)
    {
        srand((double)microtime() * 1000000);
        $user_salt = uniqid(rand());
        $data['user_salt'] = $user_salt;
        $data['user_password'] = Hash::make($user_password . $user_salt);

        return $this->edit($user_id, $data);
    }


    //注册操作
    public function insertUser($attributes)
    {
        srand((double)microtime() * 1000000);
        $user_salt = uniqid(rand());

        $user = $this->create([
            'user_account' => $attributes['name'],
            'user_password' => Hash::make($attributes['password'] . $user_salt),
            'user_salt' => $user_salt
        ]);

        return $user->user_id;
    }


    /**
     * 获取用户 不能替换函数需要传对象给auth
     * @param $where
     * @return \Closure|null
     */
    public function getUser($where)
    {
        return $this->findWhere($where)->first();
    }

}

