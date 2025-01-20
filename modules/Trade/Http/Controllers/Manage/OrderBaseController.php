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


namespace Modules\Trade\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Trade\Repositories\Criteria\OrderInfoCriteria;
use Modules\Trade\Repositories\Criteria\OrderStateLogCriteria;
use Modules\Trade\Services\OrderService;
use Modules\Trade\Services\OrderStateLogService;

class OrderBaseController extends BaseController
{
    private $orderService;
    private $orderStateLogService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderService $orderService, OrderStateLogService $orderStateLogService)
    {
        $this->orderService = $orderService;
        $this->orderStateLogService = $orderStateLogService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->orderService->list($request, new OrderInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 详情
     */
    public function detail(Request $request)
    {
        $order_id = $request['order_id'];
        $data = $this->orderService->detail($order_id);

        return Respond::success($data);
    }


    /**
     * 日志列表
     */
    public function listStateLog(Request $request)
    {
        $data = $this->orderStateLogService->list($request, new OrderStateLogCriteria($request));

        return Respond::success($data);
    }


    /**
     * 订单审核
     */
    public function review(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->review($request->get('order_id'));
        } else {
            throw new ErrorException('订单号有误！');
        }

        return Respond::success($data);
    }


    /**
     * 财务审核
     */
    public function finance(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->finance($request->get('order_id'));
        } else {
            throw new ErrorException('订单号有误！');
        }

        return Respond::success($data);
    }


    /**
     * 出库
     */
    public function picking(Request $request)
    {
        $data = $this->orderService->picking($request);

        return Respond::success($data);
    }


    /**
     * 发货操作
     */
    public function shipping(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->shipping($request);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

        return Respond::success($data);
    }


    /**
     * 确认收货
     */
    public function receive(Request $request)
    {
        if ($request->has('order_id')) {
            $data = $this->orderService->receive($request->get('order_id'));
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

        return Respond::success($data);
    }


    /**
     * 取消订单
     */
    public function cancel(Request $request)
    {
        if ($request->has('order_id')) {
            $order_state_note = $request->input('order_cancel_reason', '');
            $data = $this->orderService->cancel($request->get('order_id'), $order_state_note);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

        return Respond::success($data);
    }

}
