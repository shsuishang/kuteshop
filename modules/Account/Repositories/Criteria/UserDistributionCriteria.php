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


namespace Modules\Account\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class UserDistributionCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //推广员ID
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //推广员父级ID
        if ($user_parent_id = $this->request->get('user_parent_id')) {
            $query->where('user_parent_id', '=', $user_parent_id);
        }

        //推广员城市合伙人ID
        if ($user_partner_id = $this->request->get('user_partner_id')) {
            $query->where('user_partner_id', '=', $user_partner_id);
        }

        //角色等级
        if ($role_level_id = $this->request->get('role_level_id')) {
            $query->where('role_level_id', '=', $role_level_id);
        }

        //注册时间
        if ($user_time = $this->request->get('user_time')) {
            $query->where('user_time', '>=', $user_time);
        }

        //是否生效
        if ($user_active = $this->request->get('user_active')) {
            $query->where('user_active', '=', $user_active);
        }

        //是否城市合伙人
        if ($user_is_pt = $this->request->get('user_is_pt')) {
            $query->where('user_is_pt', '=', $user_is_pt);
        }

        //是否省代理
        if ($user_is_pa = $this->request->get('user_is_pa')) {
            $query->where('user_is_pa', '=', $user_is_pa);
        }

        //是否区代理
        if ($user_is_da = $this->request->get('user_is_da')) {
            $query->where('user_is_da', '=', $user_is_da);
        }

        //是否市代理
        if ($user_is_ca = $this->request->get('user_is_ca')) {
            $query->where('user_is_ca', '=', $user_is_ca);
        }

        //是否服务商
        if ($user_is_sp = $this->request->get('user_is_sp')) {
            $query->where('user_is_sp', '=', $user_is_sp);
        }
    }

    protected function after($model)
    {
        return $model->orderBy('user_id', 'ASC');
    }
}
