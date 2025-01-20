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
use Modules\Trade\Repositories\Criteria\OrderReturnCriteria;
use Modules\Trade\Services\OrderReturnService;

class ReturnController extends BaseController
{
    private $orderReturnService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(OrderReturnService $orderReturnService)
    {
        $this->orderReturnService = $orderReturnService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['buyer_user_id'] = $this->userId;
        $data = $this->orderReturnService->list($request, new OrderReturnCriteria($request));

        return Respond::success($data);
    }


    /**
     * 获取退单商品信息
     */
    public function returnItem(Request $request)
    {
        $order_id = $request->input('order_id', 0);
        $order_item_id = $request->input('order_item_id', 0);
        $data = $this->orderReturnService->returnItem($order_id, $order_item_id, $this->userId);

        return Respond::success($data);
    }


    /**
     * 添加退款
     */
    public function add(Request $request)
    {
        $req = $request->all();
        $data = $this->orderReturnService->addReturn($this->userId, $req);

        return Respond::success($data);
    }


    /**
     * 退单详情
     */
    public function get(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $data = $this->orderReturnService->getReturnDetail($return_id);
        if ($data['buyer_user_id'] != $this->userId) {
            throw new ErrorException(__('无操作权限!'));
        }

        return Respond::success($data);
    }


    /**
     * 取消退单
     */
    public function cancel(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $return_row = $this->orderReturnService->get($return_id);
        if ($return_row['buyer_user_id'] != $this->userId) {
            throw new ErrorException(__('无操作权限!'));
        }

        $res = $this->orderReturnService->cancel($return_id, $return_row);

        return Respond::success([$res]);
    }


    /**
     * 填写退单物流单号
     */
    public function edit(Request $request)
    {
        $return_id = $request->input('return_id', '');
        $return_row = $this->orderReturnService->get($return_id);
        if ($return_row['buyer_user_id'] != $this->userId) {
            throw new ErrorException(__('无操作权限!'));
        }

        $res = $this->orderReturnService->editReturnExpress($request, $return_id);

        return Respond::success([$res]);
    }


}
