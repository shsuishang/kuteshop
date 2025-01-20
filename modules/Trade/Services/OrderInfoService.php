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


namespace Modules\Trade\Services;

use App\Support\StateCode;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;

/**
 * Class OrderInfoService.
 *
 * @package Modules\Trade\Services
 */
class OrderInfoService extends BaseService
{

    private $configBaseRepository;

    public function __construct(OrderInfoRepository $orderInfoRepository, ConfigBaseRepository $configBaseRepository)
    {
        $this->repository = $orderInfoRepository;
        $this->configBaseRepository = $configBaseRepository;
    }


    /**
     * 自动取消订单
     * @return void
     */
    public function autoCancelOrder()
    {
        $order_ids = $this->getAutoCancelOrderId();
        if (!empty($order_ids)) {
            $orderService = app(OrderService::class);
            foreach ($order_ids as $order_id) {
                $orderService->cancel($order_id, __('超时未支付，系统自动取消'));
            }
        }
    }


    /**
     * 获取需要自动取消的订单ID
     * @return array|mixed[]
     */
    public function getAutoCancelOrderId()
    {
        $auto_cancel_time = $this->configBaseRepository->getConfig('order_autocancel_time', 24); //单位：小时
        $time = getTime();
        $column_row = [
            'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY,
            'order_is_paid' => StateCode::ORDER_PAID_STATE_NO,
            'payment_type_id' => StateCode::PAYMENT_TYPE_ONLINE,
            ['create_time', '<', ($time - $auto_cancel_time * 60 * 60 * 1000)]
        ];

        return $this->repository->findKey($column_row);
    }


    /**
     * 自动确认收货
     * @return void
     */
    public function autoReceive()
    {
        $order_ids = $this->getAutoFinishOrderId();
        if (!empty($order_ids)) {
            $orderService = app(OrderService::class);
            foreach ($order_ids as $order_id) {
                $orderService->receive($order_id, __('系统自动收货'));
            }
        }
    }


    /**
     * 获取需要自动确认售后的订单ID
     * @return array|mixed[]
     */
    public function getAutoFinishOrderId()
    {
        $order_autofinish_time = $this->configBaseRepository->getConfig('order_autofinish_time', 7); //单位：天
        $time = getTime();
        $column_row = [
            ['order_state_id', 'IN', [StateCode::ORDER_STATE_SHIPPED, StateCode::ORDER_STATE_RECEIVED]],
            ['update_time', '<', ($time - $order_autofinish_time * 60 * 60 * 24 * 1000)]
        ];

        return $this->repository->findKey($column_row);
    }

}
