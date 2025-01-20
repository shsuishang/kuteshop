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


namespace Modules\Analytics\Services;

use App\Support\StateCode;
use Modules\Analytics\Repositories\Models\AnalyticsTrade;

/**
 * Class AnalyticsTradeService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsTradeService
{
    private $analyticsTrade;

    public function __construct(AnalyticsTrade $analyticsTrade)
    {
        $this->analyticsTrade = $analyticsTrade;
    }


    /**
     * 销售额
     * @return array
     */
    public function getSalesAmount()
    {
        $today = getToday();
        $trade_is_paid = [StateCode::ORDER_PAID_STATE_PART, StateCode::ORDER_PAID_STATE_YES];
        $trade_type_id = [StateCode::TRADE_TYPE_SHOPPING, StateCode::TRADE_TYPE_FAVORABLE];

        $data['today'] = $this->analyticsTrade->getTradeAmount($today['start'], $today['end'], $trade_is_paid, $trade_type_id);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsTrade->getTradeAmount($yesterday['start'], $yesterday['end'], $trade_is_paid, $trade_type_id);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsTrade->getTradeAmount($month['start'], $month['end'], $trade_is_paid, $trade_type_id);;

        return $data;
    }

}
