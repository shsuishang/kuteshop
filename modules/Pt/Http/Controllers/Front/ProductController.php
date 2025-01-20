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


namespace Modules\Pt\Http\Controllers\Front;

use App\Support\Respond;
use App\Support\StateCode;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Pt\Repositories\Criteria\ProductCategoryCriteria;
use Modules\Pt\Repositories\Criteria\ProductIndexCriteria;
use Modules\Pt\Services\ProductBrandService;
use Modules\Pt\Services\ProductCategoryService;
use Modules\Pt\Services\ProductCommentService;
use Modules\Pt\Services\ProductIndexService;
use Modules\Shop\Services\UserProductBrowseService;

class ProductController extends BaseController
{
    private $productCategoryService;
    private $productIndexService;
    private $productCommentService;
    private $productBrandService;
    private $productBrowseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ProductCategoryService   $productCategoryService,
        ProductIndexService      $productIndexService,
        ProductCommentService    $productCommentService,
        ProductBrandService      $productBrandService,
        UserProductBrowseService $productBrowseService
    )
    {
        $this->productCategoryService = $productCategoryService;
        $this->productIndexService = $productIndexService;
        $this->productCommentService = $productCommentService;
        $this->productBrandService = $productBrandService;
        $this->productBrowseService = $productBrowseService;
    }


    public function listAllCategory(Request $request)
    {
        $request['size'] = 99999;
        $data = $this->productCategoryService->list($request, new ProductCategoryCriteria($request));
        if (!empty($data['data'])) {
            foreach ($data['data'] as $k => $item) {
                $data['data'][$k]['name'] = $item['category_name'];
                $data['data'][$k]['id'] = $item['category_id'];
                $data['data'][$k]['parent_id'] = $item['category_parent_id'];
            }
        }

        return Respond::success($data);
    }


    /**
     * 分类列表 一级分类列表
     */
    public function listCategory(Request $request)
    {
        $request['category_is_enable'] = 1;
        if (!$request->has('category_parent_id')) {
            $request['category_parent_id'] = -1;
        }
        $data = $this->productCategoryService->list($request, new ProductCategoryCriteria($request));

        return Respond::success($data);
    }


    /**
     * 树形分类列表
     */
    public function treeCategory(Request $request)
    {
        $request['category_is_enable'] = 1;
        $data = $this->productCategoryService->getTree($request['category_parent_id'], $request);

        return Respond::success($data);
    }


    /**
     * 商品列表
     */
    public function list(Request $request)
    {

        //商品分类筛选
        if ($request->input('category_id', 0)) {
            $category_id = $request->get('category_id');
            $category_ids = $this->productCategoryService->getCategoryIdByParentId($category_id, true);
            if (!empty($category_ids)) {
                $request['category_id'] = $category_ids;
            }
        }

        //商品状态-上架中的商品
        $request['product_state_id'] = StateCode::PRODUCT_STATE_NORMAL;

        $data = $this->productIndexService->list($request, new ProductIndexCriteria($request));

        return Respond::success($data);
    }


    /**
     * 商品详情
     */
    public function detail(Request $request)
    {
        $item_id = $request->input('item_id', 0);
        $district_id = $request->input('district_id', 0);
        $gb_id = $request->input('gb_id', 0);
        $user_id = User::getUserId();

        $data = $this->productIndexService->detail($item_id, $district_id, $gb_id, $user_id);

        //todo 获取商品上级分类信息
        $data['product_categorys'] = $this->productCategoryService->getParentCategory($data['category_id']);

        //todo 获取商品评论
        $comments_data = $this->productCommentService->getProductComments($data['product_id']);
        $data['last_comments'] = $comments_data['last_comments'];
        $data['last_comment'] = $comments_data['last_comment'];

        //todo 记录浏览记录
        $this->productBrowseService->addBrowser($item_id, $user_id);

        return Respond::success($data);
    }


    public function listItem(Request $request)
    {
        $item_id_str = $request->input('item_id', '');
        $item_ids = explode(',', $item_id_str);
        $request['item_id'] = 0;
        $request['item_ids'] = $item_ids;
        $request['product_ids'] = '';

        $category_id = $request->input('category_id', 0);

        // 处理分类ID
        /*if ($category_id) {
            $categoryLeafs = $this->productCategoryService->getCategoryLeafs($category_id);
            if (!empty($categoryLeafs)) {
                $input['categoryId'] = implode(',', $categoryLeafs);
                $input['categoryIds'] = $categoryLeafs;
            } else {
                $input['categoryId'] = (string)$input['categoryId'];
                $input['categoryIds'] = [$input['categoryId']];
            }
        }*/

        // 获取商品SKU列表
        $data = $this->productIndexService->listItem($request);

        return Respond::success($data);
    }


    /**
     * 获取分类筛选项
     */
    public function getSearchFilter(Request $request)
    {
        $category_id = $request->get('category_id', 0);
        $data = $this->productCategoryService->getCategoryFilter($category_id);

        return Respond::success($data);
    }


    public function brand(Request $request)
    {
        $request->merge(['front' => 1]);
        $data = $this->productBrandService->tree($request);

        return Respond::success($data);
    }


    /**
     * 商品评论
     */
    public function getComment(Request $request)
    {
        $data = $this->productCommentService->getComment($request);

        return Respond::success($data);
    }

}
