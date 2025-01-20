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

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Services\StoreExpressLogisticsService;
use Modules\Trade\Services\OrderLogisticsService;
use Modules\Trade\Repositories\Validators\OrderLogisticsValidator;

class OrderLogisticsController extends BaseController
{
    private $orderLogisticsService;
    private $orderLogisticsValidator;
    private $storeExpressLogisticsService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        OrderLogisticsService        $orderLogisticsService,
        OrderLogisticsValidator      $orderLogisticsValidator,
        StoreExpressLogisticsService $storeExpressLogisticsService
    )
    {
        $this->orderLogisticsService = $orderLogisticsService;
        $this->orderLogisticsValidator = $orderLogisticsValidator;
        $this->storeExpressLogisticsService = $storeExpressLogisticsService;
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $order_logistics_id = $request['order_logistics_id'];
        $this->orderLogisticsValidator->setId($order_logistics_id);
        $this->orderLogisticsValidator->with($request->all())->passesOrFail('create');
        $store_logistics_id = $request['logistics_id'];
        $store_logistics_row = $this->storeExpressLogisticsService->get($store_logistics_id);
        $data = $this->orderLogisticsService->edit($order_logistics_id, [
            'order_id' => $request['order_id'],   //订单编号
            'stock_bill_id' => $request['stock_bill_id'],   //出库单号
            'order_tracking_number' => $request['order_tracking_number'], //订单物流单号
            'logistics_id' => $store_logistics_id,   //对应快递公司
            'ss_id' => $request['ss_id'],     //发货地址编号
            'logistics_explain' => $request->input('logistics_explain', ''), //发货备注
            'logistics_time' => $request->input('logistics_time'),
            'express_name' => $store_logistics_row['express_name'],
            'express_id' => $store_logistics_row['express_id'],
            'logistics_phone' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
            'logistics_mobile' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
            'logistics_contacter' => $store_logistics_row['logistics_contacter'],
            'logistics_address' => $store_logistics_row['logistics_address']
        ]);

        return Respond::success($data);
    }

}
