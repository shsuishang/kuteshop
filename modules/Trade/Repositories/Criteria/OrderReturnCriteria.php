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


namespace Modules\Trade\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class OrderReturnCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //订单编号
        if ($order_id = $this->request->get('order_id')) {
            $query->where('order_id', 'like', "%$order_id%");
        }

        //退单状态
        if ($return_state_id = $this->request->get('return_state_id')) {
            $query->where('return_state_id', '=', $return_state_id);
        }

        //退单编号
        if ($return_id = $this->request->get('return_id')) {
            $query->where('return_id', 'like', "%$return_id%");
        }

        //用户ID
        if ($buyer_user_id = $this->request->get('buyer_user_id')) {
            $query->where('buyer_user_id', '=', $buyer_user_id);
        }

        //退款渠道
        if ($return_channel_code = $this->request->get('return_channel_code')) {
            $query->where('return_channel_code', '=', $return_channel_code);
        }

        //时间筛选
        if ($return_add_start = $this->request->get('return_add_start')) {
            $query->where('return_add_time', '>=', $return_add_start);
        }

        //时间筛选
        if ($return_add_end = $this->request->get('return_add_end')) {
            $query->where('return_add_time', '<=', $return_add_end);
        }

    }


    protected function after($model)
    {
        return $model->orderBy('return_add_time', 'DESC');
    }

}
