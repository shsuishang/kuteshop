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


namespace Modules\Marketing\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class ActivityBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //活动名称
        if ($activity_name = $this->request->get('activity_name')) {
            $query->where('activity_name', 'like', "%$activity_name%");
        }

        //活动状态
        $activity_state = $this->request->get('activity_state');
        if ($activity_state) {
            if (is_array($activity_state)) {
                $query->whereIn('activity_state', $activity_state);
            } else {
                $query->where('activity_state', '=', $activity_state);
            }
        }

        //活动类型
        if ($activity_type_id = $this->request->get('activity_type_id')) {
            if (is_array($activity_type_id)) {
                $query->whereIn('activity_type_id', $activity_type_id);
            } else {
                $query->where('activity_type_id', '=', $activity_type_id);
            }
        }

        //参与类型
        $activity_type = $this->request->get('activity_type');
        if ($activity_type) {
            if (is_array($activity_type)) {
                $query->whereIn('activity_type', $activity_type);
            } else {
                $query->where('activity_type', '=', $activity_type);
            }
        }

    }

    protected function after($model)
    {
        return $model->orderBy('activity_sort', 'ASC')->orderBy('activity_id', 'DESC');
    }

}
