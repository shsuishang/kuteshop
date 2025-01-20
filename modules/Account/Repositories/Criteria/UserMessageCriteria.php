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

class UserMessageCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {

        //用户昵称
        if ($user_nickname = $this->request->get('user_nickname')) {
            $query->where('user_nickname', 'like', "%$user_nickname%");
        }

        //所属用户
        if ($user_id = $this->request->get('user_id')) {
            $query->where('user_id', '=', $user_id);
        }

        //消息种类(ENUM):1-发送消息;2-接收消息
        if ($message_kind = $this->request->get('message_kind')) {
            $query->where('message_kind', '=', $message_kind);
        }

        //消息类型(ENUM):1-系统消息;2-用户消息
        if ($message_type = $this->request->get('message_type')) {
            $query->where('message_type', '=', $message_type);
        }

        //消息时间
        if ($start_time = $this->request->get('start_time')) {
            $query->where('message_time', '>=', $start_time);
        }

        if ($this->request->has('message_is_read')) {
            $message_is_read = $this->request->get('message_is_read');
            $query->where('message_is_read', '=', $message_is_read);
        }

    }

    protected function after($model)
    {
        return $model->orderBy('message_id', 'DESC')->orderBy('message_time', 'DESC');
    }
}
