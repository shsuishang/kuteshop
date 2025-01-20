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


namespace Modules\Pay\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use Modules\Pay\Repositories\Models\ConsumeTrade;

/**
 * Class ConsumeTradeRepositoryEloquent.
 *
 * @package Modules\Pay\Repositories\Eloquent
 */
class ConsumeTradeRepositoryEloquent extends BaseRepository implements ConsumeTradeRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ConsumeTrade::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /*
     * 创建交易订单
     * @param int $user_id
     * @param array $order_row
     * @param int $trade_type_id
     * @return ConsumeTrade
     */
    public function createConsumeTrade(int $user_id, array $order_row, int $trade_type_id = StateCode::TRADE_TYPE_SHOPPING): ConsumeTrade
    {
        $buyer_store_id = 0;
        $seller_id = 10001;

        $consume_trade = [
            'trade_title' => $order_row['order_title'],
            'order_id' => $order_row['order_id'],
            'buyer_id' => $user_id,
            'buyer_store_id' => $buyer_store_id,
            'store_id' => $order_row['store_id'],
            'subsite_id' => $order_row['subsite_id'],
            'seller_id' => $seller_id,
            'chain_id' => $order_row['chain_id'],
            'trade_is_paid' => StateCode::ORDER_PAID_STATE_NO,
            'trade_type_id' => $trade_type_id,
            'payment_channel_id' => 0,
            'trade_mode_id' => 1,
            'currency_id' => $order_row['currency_id'],
            'currency_symbol_left' => $order_row['currency_symbol_left'],
            'order_payment_amount' => $order_row['order_payment_amount'],
            'order_commission_fee' => $order_row['order_commission_fee'],
            'trade_payment_amount' => $order_row['order_payment_amount'],
            'trade_payment_money' => 0,
            'trade_payment_recharge_card' => 0,
            'trade_payment_points' => 0,
            'trade_payment_sp' => 0,
            'trade_payment_credit' => 0,
            'trade_payment_redpack' => 0,
            'trade_discount' => $order_row['order_discount_amount'],
            'trade_amount' => $order_row['order_item_amount'],
            'trade_create_time' => getTime()
        ];

        return $this->add($consume_trade);
    }
}
