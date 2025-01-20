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


namespace Modules\Sys\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Kuteshop\Core\Repository\Criteria\Criteria;

class CurrencyBaseCriteria extends Criteria
{
    protected function condition(Builder $query): void
    {
        //默认汇率
        if ($currency_is_default = $this->request->get('currency_is_default')) {
            $query->where('currency_is_default', '=', $currency_is_default);
        }

        //默认语言
        if ($currency_default_lang = $this->request->get('currency_default_lang')) {
            $query->where('currency_default_lang', '=', $currency_default_lang);
        }

        //默认UI
        if ($currency_is_standard = $this->request->get('currency_is_standard')) {
            $query->where('currency_is_standard', '=', $currency_is_standard);
        }

        //主键
        if ($currency_id = $this->request->get('currency_id')) {
            $query->where('currency_id', '=', $currency_id);
        }

        //是否开启
        if ($currency_status = $this->request->get('currency_status')) {
            $query->where('currency_status', '=', $currency_status);
        }

        //名称
        if ($currency_title = $this->request->get('currency_title')) {
            $query->where('currency_title', 'like', "%$currency_title%");
        }
    }


    protected function after($model)
    {
        return $model->orderBy('currency_sort', 'ASC')->orderBy('currency_id', 'ASC');
    }

}
