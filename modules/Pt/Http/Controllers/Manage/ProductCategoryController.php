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
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pt\Repositories\Criteria\ProductCategoryCriteria;
use Modules\Pt\Services\ProductCategoryService;
use Modules\Pt\Repositories\Validators\ProductCategoryValidator;

class ProductCategoryController extends BaseController
{
    private $productCategoryService;
    private $productCategoryValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductCategoryService $productCategoryService, ProductCategoryValidator $productCategoryValidator)
    {
        $this->productCategoryService = $productCategoryService;
        $this->productCategoryValidator = $productCategoryValidator;
    }


    /**
     * 列表
     */
    public function tree(Request $request)
    {
        $data = $this->productCategoryService->tree($request);

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->productCategoryService->list($request, new ProductCategoryCriteria($request));

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
            'category_name' => $request['category_name'],   //分类名称
            'category_parent_id' => $request->input('category_parent_id', 0), //上级分类编号
            'category_image' => $request->input('category_image', ''),   //分类图片
            'type_id' => $request->input('type_id', 0),   //所属类型编号
            'category_commission_rate' => $request->input('category_commission_rate', 0),   //分佣比例
            'category_sort' => $request->input('category_sort', 0), //排序
            'category_is_enable' => $request->boolean('category_is_enable') //是否启用(BOOL):0-不显示;1-显示
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->productCategoryValidator->with($request->all())->passesOrFail('create');
        $data = $this->productCategoryService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $category_id = $request->get('category_id', -1);
        $this->productCategoryValidator->setId($category_id);
        $this->productCategoryValidator->with($request->all())->passesOrFail('update');
        $data = $this->productCategoryService->edit($category_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $category_id = $request->get('category_id', -1);
        $data = $this->productCategoryService->remove($category_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $category_id = $request->get('category_id', -1);
        $state_data = [];
        if ($request->has('category_is_enable')) {
            $state_data['category_is_enable'] = $request->boolean('category_is_enable');
        }

        //todo 变更相关状态
        if ($state_data) {
            $data = $this->productCategoryService->edit($category_id, $state_data);
        } else {
            throw new ErrorException(__('无修改数据'));
        }

        return Respond::success($data);
    }
}
