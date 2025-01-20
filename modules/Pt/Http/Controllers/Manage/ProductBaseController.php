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


namespace Modules\Pt\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use App\Support\StateCode;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Pt\Repositories\Criteria\ProductIndexCriteria;
use Modules\Pt\Repositories\Validators\ProductBaseValidator;
use Modules\Pt\Services\ProductBaseService;
use Modules\Pt\Services\ProductIndexService;

class ProductBaseController extends BaseController
{
    private $productBaseService;
    private $productIndexService;
    private $productBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ProductBaseService   $productBaseService,
        ProductIndexService  $productIndexService,
        ProductBaseValidator $productBaseValidator)
    {
        $this->productBaseService = $productBaseService;
        $this->productIndexService = $productIndexService;
        $this->productBaseValidator = $productBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productIndexService->list($request, new ProductIndexCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增/修改商品信息
     */
    public function save(Request $request)
    {
        $this->productBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->productBaseService->saveProduct($request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->productBaseService->removeProduct($request->get('product_id'));

        return Respond::success($data);
    }


    /**
     * editState
     */
    public function editState(Request $request)
    {
        $data = $this->productBaseService->editState($request);

        return Respond::success($data);
    }


    /**
     *  更新产品佣金比例
     */
    public function editCommissionRate(Request $request)
    {
        $product_id = $request->get('product_id', -1);

        $edit_data = [];
        if ($request->has('product_commission_rate')) {
            $edit_data['product_commission_rate'] = $request['product_commission_rate'];
        }

        // 更新数据
        if ($product_id && !empty($edit_data)) {
            $result = $this->productBaseService->edit($product_id, $edit_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return Respond::success($edit_data);
    }

    /**
     * 更新商品排序值
     */
    public function editSort(Request $request)
    {
        $product_id = $request->get('product_id', -1);
        $edit_data = [];

        if ($request->has('product_order')) {
            $edit_data['product_order'] = $request['product_order'];
        }

        // 更新数据
        if ($product_id && !empty($edit_data)) {
            $result = $this->productBaseService->edit($product_id, $edit_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return Respond::success($edit_data);
    }


    /**
     * 获取商品信息
     */
    public function getProduct(Request $request)
    {
        $product_id = $request->get('product_id');
        $data = $this->productBaseService->getProduct($product_id);

        return Respond::success($data);
    }


    /**
     * 获取商品SKU列表
     */
    public function listItem(Request $request)
    {
        $request['product_state_id'] = StateCode::PRODUCT_STATE_NORMAL;
        $request['product_verify_id'] = StateCode::PRODUCT_VERIFY_PASSED;
        $item_id = $request->input('item_id', '');
        unset($request['item_id']);

        $item_ids = explode(',', $item_id);
        $request->merge(['item_ids' => $item_ids]);

        $data = $this->productIndexService->listItem($request);

        return Respond::success($data);
    }


    public function batchEditState(Request $request)
    {
        $product_ids_str = $request->input('product_ids', '');
        $product_ids = explode(',', $product_ids_str);
        $product_state_id = $request->input('product_state_id', 0);
        $affected_rows = $this->productBaseService->batchEditState($product_ids, $product_state_id);
        $msg = __('成功更新了') . $affected_rows . __('个商品');

        return Respond::success($affected_rows, $msg);
    }

}
