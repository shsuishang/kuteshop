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


namespace Modules\Pay\Services;

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Pay\Repositories\Contracts\ConsumeDepositRepository;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use App\Exceptions\ErrorException;
use Modules\Pay\Repositories\Contracts\UserPayRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Trade\Repositories\Contracts\OrderDataRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;
use Modules\Trade\Repositories\Contracts\OrderItemRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnItemRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnRepository;
use Modules\Trade\Services\OrderService;
use Yansongda\Pay\Pay;

/**
 * Class ConsumeReturnService.
 *
 * @package Modules\Pay\Services
 */
class ConsumeReturnService extends BaseService
{
    private $orderDataRepository;
    private $orderInfoRepository;
    private $orderItemRepository;
    private $userInfoRepository;
    private $orderReturnRepository;
    private $orderReturnItemRepository;
    private $consumeDepositRepository;


    private $consumeTradeRepository;
    private $consumeRecordRepository;
    private $userResourceRepository;
    private $userPayRepository;
    private $orderService;
    private $configBaseService;

    public function __construct(
        OrderDataRepository       $orderDataRepository,
        OrderInfoRepository       $orderInfoRepository,
        OrderItemRepository       $orderItemRepository,
        UserInfoRepository        $userInfoRepository,
        OrderReturnRepository     $orderReturnRepository,
        OrderReturnItemRepository $orderReturnItemRepository,
        ConsumeRecordRepository   $consumeRecordRepository,
        ConsumeDepositRepository  $consumeDepositRepository,

        ConsumeTradeRepository    $consumeTradeRepository,
        UserResourceRepository    $userResourceRepository,
        UserPayRepository         $userPayRepository,
        OrderService              $orderService,
        ConfigBaseService         $configBaseService
    )
    {
        $this->orderDataRepository = $orderDataRepository;
        $this->orderInfoRepository = $orderInfoRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->orderReturnItemRepository = $orderReturnItemRepository;
        $this->consumeRecordRepository = $consumeRecordRepository;
        $this->consumeDepositRepository = $consumeDepositRepository;

        $this->consumeTradeRepository = $consumeTradeRepository;
        $this->userResourceRepository = $userResourceRepository;
        $this->userPayRepository = $userPayRepository;

        $this->orderService = $orderService;
        $this->configBaseService = $configBaseService;
    }


    /**
     * 执行退款操作
     *
     * @param array $order_returns
     * @return bool
     * @throws ErrorException
     */
    public function doRefund($order_returns)
    {
        $paid_return_ids = array();

        // 原理退回标记
        $order_refund_flag = $this->configBaseService->getConfig("order_refund_flag", false);
        $order_ids = array_column_unique($order_returns, 'order_id');
        $user_ids = array_column_unique($order_returns, 'buyer_user_id');
        $return_ids = array_column_unique($order_returns, 'return_id');

        $order_data_rows = $this->orderDataRepository->gets($order_ids);
        $order_info_rows = $this->orderInfoRepository->gets($order_ids); //findWhereIn('order_id', $order_ids)->keyBy('order_id')->toArray()
        $user_resource_rows = $this->userResourceRepository->gets($user_ids);
        $user_info_rows = $this->userInfoRepository->gets($user_ids);

        $order_return_items = $this->orderReturnItemRepository->find([['return_id', 'IN', $return_ids]]);
        $order_item_ids = array_column_unique($order_return_items, 'order_item_id');
        $order_item_list = $this->orderItemRepository->find([['order_item_id', 'IN', $order_item_ids]]);
        $order_return_items = arrayMap($order_return_items, 'return_id');


        // 积分抵扣，暂时忽略，不涉及此处支付。
        // 按照次序，依次支付。
        foreach ($order_returns as $order_return) {

            $store_id = $order_return['store_id'];
            $return_id = $order_return['return_id'];
            $order_id = $order_return['order_id'];
            $user_id = $order_return['buyer_user_id'];
            if (!$user_id) {
                throw new ErrorException(__("买家信息有误"));
            }

            $user_resource = $user_resource_rows[$user_id];
            $user_info = $user_info_rows[$user_id];
            $order_info = $order_info_rows[$order_id];
            $order_data = $order_data_rows[$order_id];

            // 判断是否需要退佣金
            $return_commission_fee = 0;

            // 不是退运费
            $return_is_shipping_fee = $order_return['return_is_shipping_fee'];
            if (!$return_is_shipping_fee) {
                $withdraw_received_day = $this->configBaseService->getConfig("withdraw_received_day", 7);
                if ($withdraw_received_day >= 0) {
                    $order_state_id = $order_info['order_state_id'];
                    $order_is_paid = $order_info['order_is_paid'];

                    // 未到可结算时间可退佣金
                    if (!$order_state_id == StateCode::ORDER_STATE_FINISH && !$order_is_paid == StateCode::ORDER_PAID_STATE_YES) {
                        $return_commission_fee = $order_return['return_commission_fee'];
                    }
                }
            }

            $waiting_refund_amount = $order_return['return_refund_amount'];
            if ($waiting_refund_amount) {

                $return_items = $order_return_items[$return_id];

                //todo 修改订单商品 退款数据
                foreach ($return_items as $return_item) {
                    $order_item_id = $return_item['order_item_id'];
                    $order_item = $order_item_list[$order_item_id];

                    $order_item_return_agree_amount = $order_item['order_item_return_agree_amount'] + $return_item['return_item_subtotal'];
                    $order_item_return_agree_num = $order_item['order_item_return_agree_num'] + $return_item['return_item_num'];
                    $order_item_commission_fee_refund = $order_item['order_item_commission_fee_refund'] + $return_item['return_item_commision_fee'];

                    $this->orderItemRepository->edit($order_item_id, [
                        'order_item_return_agree_amount' => $order_item_return_agree_amount,
                        'order_item_return_agree_num' => $order_item_return_agree_num,
                        'order_item_commission_fee_refund' => $order_item_commission_fee_refund
                    ]);
                }

                //todo 订单data需要修改的数据
                $order_data_edit = [
                    'order_refund_agree_amount' => $order_data['order_refund_agree_amount'] + $waiting_refund_amount
                ];
                if ($return_commission_fee) {
                    $order_data_edit['order_commission_fee_refund'] = $order_data['order_commission_fee_refund'] + $return_commission_fee;
                }

                $trade = [
                    'order_id' => $return_id,
                    'store_id' => $order_return['store_id'],
                    'chain_id' => 0,
                    'user_nickname' => $user_info['user_nickname'],
                    'trade_title' => __("退款单:") . $return_id,
                    'trade_desc' => '',
                    'record_total' => $waiting_refund_amount,
                    'record_money' => $waiting_refund_amount,
                ];
                $deposit = [
                    'payment_type_id' => StateCode::PAYMENT_TYPE_ONLINE,
                    'payment_channel_id' => StateCode::PAYMENT_MET_MONEY
                ];

                //todo 1、增加买家流水记录
                $this->consumeRecordRepository->addConsumeRecord($user_id, $trade, $deposit, StateCode::TRADE_TYPE_REFUND_GATHERING);

                $consume_deposit = $this->consumeDepositRepository->findOne(['order_id' => $order_id]);
                if ($order_refund_flag && !empty($consume_deposit) &&
                    in_array($consume_deposit['payment_channel_id'], [StateCode::PAYMENT_CHANNEL_WECHAT, StateCode::PAYMENT_CHANNEL_ALIPAY])) {
                    //原路退回
                    $deposit_total_fee = $consume_deposit['deposit_total_fee'];
                    $online_refund_amount = ($deposit_total_fee < $waiting_refund_amount) ? $deposit_total_fee : $waiting_refund_amount;
                    $this->doOnlineRefund($consume_deposit['payment_channel_id'], $order_id, $online_refund_amount, $return_id);
                } else {
                    //$order_data_edit['order_refund_agree_money'] = $order_data['order_refund_agree_money'] + $waiting_refund_amount;

                    //todo 2、增加买家余额
                    $user_money = $user_resource['user_money'] + $waiting_refund_amount;
                    $this->userResourceRepository->edit($user_id, ['user_money' => $user_money]);
                }

                //todo 扣除商家余额
                $consume_trade = $this->consumeTradeRepository->findOne(['order_id' => $order_id]);
                if (!empty($consume_trade) && $consume_trade['seller_id']) {
                    $trade['record_total'] = -$trade['record_total'];
                    $trade['record_money'] = -$trade['record_money'];
                    $this->consumeRecordRepository->addConsumeRecord($consume_trade['seller_id'], $trade, $deposit, StateCode::TRADE_TYPE_REFUND_PAY);
                    $this->userResourceRepository->incrementFieldByIds([$consume_trade['seller_id']], 'user_money', $trade['record_total']);
                }


                //todo 修改订单数据
                $this->orderDataRepository->edit($order_id, $order_data_edit);

                $paid_return_ids[] = $return_id;
            }
        }

        if (!empty($paid_return_ids)) {
            if (!$this->setReturnPaidYes($paid_return_ids)) {
                throw new ErrorException(__('退款失败'));
            }
        }

        return true;
    }


    /**
     * 修改为退款已支付状态
     *
     * @param array $return_ids
     * @return bool
     * @throws ErrorException
     */
    public function setReturnPaidYes($return_ids)
    {
        /*if (empty($return_ids)) return false;

        $orderReturn = new OrderReturn();
        $orderReturn->setReturnIsPaid(true);
        $orderReturn->setReturnIsPaid(false);

        $returnQueryWrapper = new QueryWrapper();
        $returnQueryWrapper->in("return_id", $return_ids);
        if (!$this->orderReturnRepository->edit($orderReturn, $returnQueryWrapper)) {
            throw new ErrorException(ResultCode::FAILED);
        }*/

        /*$orderReturnList = $this->orderReturnRepository->gets($return_ids);
        $orderIds = array_unique(array_map(function($orderReturn) { return $orderReturn->getOrderId(); }, $orderReturnList));

        if (empty($orderIds)) return false;

        // 判断是否存在用金额退款
        $orderQueryWrapper = new QueryWrapper();
        $orderQueryWrapper->in("order_id", $orderIds)->eq("uo_active", 1);
        $distributionOrderList = $this->distributionOrderRepository->find($orderQueryWrapper);
        $userIds = array_unique(array_map(function($distributionOrder) { return $distributionOrder->getUserId(); }, $distributionOrderList));
        $commissionList = $this->distributionCommissionRepository->gets($userIds);

        if (!empty($distributionOrderList)) {
            foreach ($distributionOrderList as $distributionOrder) {
                $uoBuyCommission = $distributionOrder->getUoBuyCommission();
                $uoDirectsellerCommission = $distributionOrder->getUoDirectsellerCommission();
                $addCommissionRefundAmount = NumberUtil::add($uoBuyCommission, $uoDirectsellerCommission);

                $userId = $distributionOrder->getUserId();
                $commission = array_values(array_filter($commissionList, function($distributionCommission) use ($userId) { return $distributionCommission->getUserId() == $userId; }))[0];

                $commissionRefundAmount = !empty($commission->getCommissionRefundAmount()) ? $commission->getCommissionRefundAmount() : 0;
                $commission->setCommissionRefundAmount(NumberUtil::add($addCommissionRefundAmount, $commissionRefundAmount));
            }

            if (!empty($commissionList)) {
                if (!$this->distributionCommissionRepository->edit($commissionList)) {
                    throw new ErrorException(ResultCode::FAILED);
                }
            }

            $uoIds = array_unique(array_map(function($distributionOrder) { return $distributionOrder->getUoId(); }, $distributionOrderList));
            $distributionOrder = new DistributionOrder();
            $distributionOrder->setUoActive(true);
            $distributionOrderQueryWrapper = new QueryWrapper();
            $distributionOrderQueryWrapper->in("uo_id", $uoIds);
            if (!$this->distributionOrderRepository->edit($distributionOrder, $distributionOrderQueryWrapper)) {
                throw new ErrorException(ResultCode::FAILED);
            }

            $distributionOrderItem = new DistributionOrderItem();
            $distributionOrderItem->setUoiActive(true);
            $orderItemQueryWrapper = new QueryWrapper();
            $orderItemQueryWrapper->in("order_id", $orderIds);
            if (!$this->distributionOrderItemRepository->edit($distributionOrderItem, $orderItemQueryWrapper)) {
                throw new ErrorException(ResultCode::FAILED);
            }
        }*/

        return true;
    }


    public function doOnlineRefund($payment_channel_id, $order_id, $online_refund_amount, $return_id)
    {
        if ($payment_channel_id == StateCode::PAYMENT_CHANNEL_ALIPAY) {
            $result = $this->doAlipayRefund($order_id, $online_refund_amount);
        }

        if ($payment_channel_id == StateCode::PAYMENT_CHANNEL_WECHAT) {
            $result = $this->doWechatRefund($order_id, $online_refund_amount);
        }

        $this->orderReturnRepository->edit($return_id, [
            'return_channel_code' => $result['return_channel_code'],
            'return_channel_flag' => 1,
            'return_channel_time' => getDateTime(),
            'return_channel_trans_id' => isset($result['out_refund_no']) ? $result['out_refund_no'] : $order_id,
            'deposit_trade_no' => $result['deposit_trade_no'],
            'payment_channel_id' => $payment_channel_id
        ]);
    }


    public function doAlipayRefund($out_trade_no, $amount)
    {
        $alipay_config = $this->configBaseService->getAlipayConfig();
        $alipay = Pay::alipay($alipay_config);

        $result_object = $alipay->refund([
            'out_trade_no' => $out_trade_no,
            'refund_amount' => $amount
        ]);

        $result = $result_object->toArray();
        Log::info($result);

        $result['deposit_trade_no'] = $result['trade_no'];
        $result['return_channel_code'] = 'alipay';

        return $result;
    }


    //微信原来退款
    public function doWechatRefund($out_trade_no, $amount)
    {
        $wechat_config = $this->configBaseService->getWechatConfig();
        $wechat = Pay::wechat($wechat_config);

        $result_object = $wechat->refund([
            'out_trade_no' => $out_trade_no,
            'out_refund_no' => (string)getTime(),
            'amount' => [
                'refund' => $amount * 100,
                'total' => $amount * 100,
                'currency' => 'CNY',
            ]
        ]);
        Log::info($result_object);

        $result = $result_object->toArray();
        $result['deposit_trade_no'] = $result['transaction_id'];
        $result['return_channel_code'] = 'wxpay';

        return $result;
    }


}
