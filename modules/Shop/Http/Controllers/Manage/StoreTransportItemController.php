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
use Modules\Shop\Repositories\Criteria\StoreTransportItemCriteria;
use Modules\Shop\Services\StoreTransportItemService;
use Modules\Shop\Repositories\Validators\StoreTransportItemValidator;

class StoreTransportItemController extends ShopController
{
    private $storeTransportItemService;
    private $storeTransportItemValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(StoreTransportItemService $storeTransportItemService, StoreTransportItemValidator $storeTransportItemValidator)
    {
        $this->storeTransportItemService = $storeTransportItemService;
        $this->storeTransportItemValidator = $storeTransportItemValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeTransportItemService->list($request, new StoreTransportItemCriteria($request));

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeTransportItemValidator->with($request->all())->passesOrFail($action);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'transport_type_id' => $request['transport_type_id'],   //模板编号
            'transport_item_default_num' => $request->input('transport_item_default_num', 1),   //默认数量
            'transport_item_default_price' => $request->input('transport_item_default_price', 0), //默认运费
            'transport_item_add_num' => $request->input('transport_item_add_num', 1),       //增加数量
            'transport_item_add_price' => $request->input('transport_item_add_price', 0),     //增加运费
            'transport_item_city_ids' => $request->input('transport_item_city_ids', ''), //区域城市id(DOT)
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
        $data = $this->storeTransportItemService->add($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $transport_item_id = $request['transport_item_id'];
        $this->validateRequest($request, 'update');

        if (!$request->has('transport_type_id') && $request->has('transport_item_city_ids')) {
            $data = $this->storeTransportItemService->edit($transport_item_id, [
                'transport_item_city_ids' => $request['transport_item_city_ids']
            ]);
        } else {
            $formatted_request = $this->formatRequest($request);
            $data = $this->storeTransportItemService->edit($transport_item_id, $formatted_request);
        }

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $transport_item_id = $request['transport_item_id'];
        $data = $this->storeTransportItemService->remove($transport_item_id);

        return Respond::success($data);
    }
}
