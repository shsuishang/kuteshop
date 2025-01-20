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


namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\StoreExpressLogisticsCriteria;
use Modules\Shop\Repositories\Validators\StoreExpressLogisticsValidator;
use Modules\Shop\Services\StoreExpressLogisticsService;

class StoreExpressLogisticsController extends ShopController
{
    private $storeExpressLogisticsService;
    private $storeExpressLogisticsValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreExpressLogisticsService $storeExpressLogisticsService, StoreExpressLogisticsValidator $storeExpressLogisticsValidator)
    {
        $this->storeExpressLogisticsService = $storeExpressLogisticsService;
        $this->storeExpressLogisticsValidator = $storeExpressLogisticsValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeExpressLogisticsService->list($request, new StoreExpressLogisticsCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'logistics_name' => $request['logistics_name'],   //物流名称
            'express_id' => $request['express_id'],       //快递编号
            'express_name' => $request['express_name'],     //快递名称
            'logistics_number' => $request->input('logistics_number', 0),   //公司编号
            'logistics_fee' => $request->input('logistics_fee', 0),   //物流运费
            'logistics_intl' => $request->input('logistics_intl', '+86'), //国家编号
            'logistics_mobile' => $request->input('logistics_mobile', ''),    //手机号码
            'logistics_contacter' => $request->input('logistics_contacter', ''), //联系人
            'logistics_address' => $request->input('logistics_address', ''),   //联系地址
            'logistics_is_enable' => $request->boolean('logistics_is_enable'),   //是否启用(BOOL):1-启用;0-禁用
            'logistics_is_default' => $request->boolean('logistics_is_default'),  //是否为默认(BOOL):1-默认;0-非默认
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validateRequest($request, 'create');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeExpressLogisticsService->addExpressLogistics($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $logistics_id = $request['logistics_id'];
        $this->validateRequest($request, 'update');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeExpressLogisticsService->editExpressLogistics($logistics_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $logistics_id = $request->input('logistics_id', 0);
        $data = $this->storeExpressLogisticsService->remove($logistics_id);

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeExpressLogisticsValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $data = $this->storeExpressLogisticsService->editState($request);

        return Respond::success($data);
    }

}
