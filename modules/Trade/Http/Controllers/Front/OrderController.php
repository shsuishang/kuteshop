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


namespace Modules\Trade\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Repositories\Criteria\OrderInfoCriteria;
use Modules\Trade\Repositories\Criteria\OrderInvoiceCriteria;
use Modules\Trade\Services\OrderCommentService;
use Modules\Trade\Services\OrderInvoiceService;
use Modules\Trade\Services\OrderService;
use Modules\Trade\Services\UserCartService;

class OrderController extends BaseController
{
    private $orderService;
    private $userCartService;
    private $orderCommentService;
    private $orderInvoiceService;

    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        OrderService        $orderService,
        UserCartService     $userCartService,
        OrderCommentService $orderCommentService,
        OrderInvoiceService $orderInvoiceService
    )
    {
        $this->orderService = $orderService;
        $this->userCartService = $userCartService;
        $this->orderCommentService = $orderCommentService;
        $this->orderInvoiceService = $orderInvoiceService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->orderService->list($request, new OrderInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 详情
     */
    public function detail(Request $request)
    {
        $request['user_id'] = $this->userId;
        $order_id = $request->input('order_id');
        $data = $this->orderService->detail($order_id);

        return Respond::success($data);
    }


    /**
     * 获取用户中心订单数量
     */
    public function getOrderNum(Request $request)
    {
        $data = $this->orderService->getOrderStatisticsInfo($this->userId);

        return Respond::success($data);
    }


    /**
     * 添加订单
     */
    public function add(Request $request)
    {
        $cart_data = $request->all();
        $cart_data['user_id'] = $this->userId;
        $cart_data['cart_select'] = 1;
        $cart_data['site_id'] = $request->input('site_id', 0);
        $cart_data['delivery_time_id'] = $request->input('delivery_time_id', 0);
        $cart_data['invoice_type_id'] = $request->input('invoice_type_id', 0);
        $cart_data['order_invoice_title'] = $request->input('order_invoice_title', '');
        $cart_data['user_invoice_id'] = $request->input('user_invoice_id', 0);
        $cart_data['salesperson_id'] = $request->input('salesperson_id', 0);
        $cart_data['virtual_service_date'] = $request->input('virtual_service_date', '');
        $cart_data['virtual_service_time'] = $request->input('virtual_service_time', '');

        $store_rows = $this->userCartService->checkout($request, $this->userId);
        $cart_data = array_merge($cart_data, $store_rows);

        $cart_data = $this->orderService->addOrder($cart_data, $this->userId);

        return Respond::success($cart_data);
    }


    /**
     * 取消订单
     */
    public function cancel(Request $request)
    {
        $order_id = $request->input('order_id', '');
        $this->orderService->checkUserOrder($this->userId, $order_id);

        $flag = $this->orderService->cancel($order_id);
        if ($flag) {
            return Respond::success([]);
        } else {
            return Respond::error(__('取消订单失败'));
        }
    }


    /**
     * 确认收货
     */
    public function receive(Request $request)
    {
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $this->orderService->checkUserOrder($this->userId, $order_id);
            $data = $this->orderService->receive($order_id);

            return Respond::success($data);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }

    }


    /**
     * 订单商品评价
     */
    public function storeEvaluationWithContent(Request $request)
    {
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $this->orderService->checkUserOrder($this->userId, $order_id);
            $data = $this->orderCommentService->storeEvaluationWithContent($order_id, $this->userId);

            return Respond::success($data);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }
    }


    /**
     * 添加订单评论
     */
    public function addOrderComment(Request $request)
    {
        if ($request->has('order_id')) {
            $order_id = $request->get('order_id');
            $this->orderService->checkUserOrder($this->userId, $order_id);
            $data = $this->orderCommentService->addOrderComment($this->userId, $request);

            return Respond::success($data);
        } else {
            throw new ErrorException(__('订单号有误！'));
        }
    }


    /**
     * 订单发票列表
     */
    public function listInvoice(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->orderInvoiceService->list($request, new OrderInvoiceCriteria($request));

        return Respond::success($data);
    }


    /**
     * 添加订单发票
     */
    public function addOrderInvoice(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->orderService->addOrderInvoice($request);

        return Respond::success($data);
    }


}
