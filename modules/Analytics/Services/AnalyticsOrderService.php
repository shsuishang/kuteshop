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
use Modules\Analytics\Repositories\Models\AnalyticsOrder;
use Modules\Analytics\Repositories\Models\AnalyticsProduct;
use Modules\Analytics\Repositories\Models\AnalyticsUser;

/**
 * Class AnalyticsOrderService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsOrderService
{
    private $analyticsOrder;
    private $analyticsUser;
    private $analyticsProduct;

    public function __construct(AnalyticsOrder $analyticsOrder, AnalyticsUser $analyticsUser, AnalyticsProduct $analyticsProduct)
    {
        $this->analyticsOrder = $analyticsOrder;
        $this->analyticsUser = $analyticsUser;
        $this->analyticsProduct = $analyticsProduct;
    }


    /**
     * 获取订单数量
     * @return array
     */
    public function getOrderNum()
    {
        $today = getToday();

        //统计没有取消的订单
        $order_state_ids = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_PAID,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING,
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH,
            StateCode::ORDER_STATE_SELF_PICKUP
        ];
        $order_is_paids = [StateCode::ORDER_PAID_STATE_PART, StateCode::ORDER_PAID_STATE_YES];

        $data['today'] = $this->analyticsOrder->getOrderNum($today['start'], $today['end'], $order_state_ids, $order_is_paids);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsOrder->getOrderNum($yesterday['start'], $yesterday['end'], $order_state_ids, $order_is_paids);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据 * 100%
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsOrder->getOrderNum($month['start'], $month['end'], $order_state_ids, $order_is_paids);

        return $data;
    }


    public function getOrderAmount($request)
    {
        //统计没有取消的订单
        $order_state_id = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_PAID,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING,
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH,
            StateCode::ORDER_STATE_SELF_PICKUP
        ];
        $request['order_state_id'] = $order_state_id;

        $request['order_is_paid'] = [
            StateCode::ORDER_PAID_STATE_PART,
            StateCode::ORDER_PAID_STATE_YES
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');

        // 获取当前周期内数据
        $current = $this->analyticsOrder->getOrderAmount($request);
        $data['current'] = $current;
        $data['pre'] = 0;
        $data['daym2m'] = 0;
        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $request['stime'] = $pre_stime;
            $request['etime'] = $stime;
            $pre_reg_num = $this->analyticsOrder->getOrderAmount($request);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    //运营首页-面板数据
    public function getDashboardTimeLine($request)
    {
        $dashboard = [];
        $stime = $request->input('stime', 0);
        $etime = $request->input('etime', 0);

        // 设置响应数据
        $dashboard['order_time_line'] = $this->analyticsOrder->getOrderTimeLine($stime, $etime);
        $dashboard['user_time_line'] = $this->analyticsUser->getUserTimeLine($stime, $etime);
        $dashboard['pt_time_line'] = $this->analyticsProduct->getProductTimeLine($stime, $etime);
        $dashboard['pay_time_line'] = $this->analyticsOrder->getPayTimeLine($stime, $etime);

        return $dashboard;
    }


    public function getOrderNumDate($days)
    {
        $data = $this->analyticsOrder->getOrderNumDate($days);

        return $data;
    }


    public function getSaleOrderAmount($request)
    {
        $stime = $request->input('stime', 0);
        $etime = $request->input('etime', 0);
        $data = $this->analyticsOrder->getSaleOrderAmount($stime, $etime);

        return $data;
    }


    public function getCustomerTimeline($days)
    {
        $result = $this->analyticsOrder->getOrderNumDate($days);

        return $result;
    }

    public function getOrderCustomerNumTimeline($request)
    {
        // 获取请求中的时间参数
        $stime = $request->input('stime', 0);
        $etime = $request->input('etime', 0);
        $result = $this->analyticsOrder->getOrderCustomerNumTimeline($stime, $etime);

        return $result;
    }


    public function getOrderNumTimeline($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $result = $this->analyticsOrder->getOrderTimeLine($stime, $etime);

        return $result;
    }

    public function getOrderItemNumTimeLine($request)
    {

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $result = $this->analyticsOrder->getOrderItemNumTimeLine($stime, $etime);

        return $result;
    }

    public function getOrderItemNum($request)
    {
        $data = [
            'pre' => 0,
            'daym2m' => 0
        ];

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data['current'] = $current = $this->analyticsOrder->getOrderItemNum($request);

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $request['stime'] = $pre_stime;
            $request['etime'] = $pre_etime;
            $pre_reg_num = $this->analyticsOrder->getOrderItemNum($request);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    public function listOrderItemNum($request)
    {
        $data = $this->analyticsOrder->listOrderItemNum($request);

        return $data;
    }

}
