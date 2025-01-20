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


namespace Modules\Cms\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Cms\Repositories\Validators\ArticleCategoryValidator;
use Modules\Cms\Services\ArticleCategoryService;

class ArticleCategoryController extends Controller
{
    private $articleCategoryService;
    private $articleCategoryValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArticleCategoryService $articleCategoryService, ArticleCategoryValidator $articleCategoryValidator)
    {
        $this->articleCategoryService = $articleCategoryService;
        $this->articleCategoryValidator = $articleCategoryValidator;
    }


    /**
     * 分类列表
     */
    public function tree(Request $request)
    {
        $data = $this->articleCategoryService->tree($request);

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'category_name' => $request['category_name'],       //分类名称
            'category_parent_id' => $request->input('category_parent_id', 0),  //上级编号
            'category_image_url' => $request->input('category_image_url', ''),  //分类图标
            'category_desc' => $request['category_desc'],       //分类描述
            'category_order' => $request->input('category_order', 0)       //分类排序
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->articleCategoryValidator->with($request->all())->passesOrFail('create');

        $data = $this->articleCategoryService->addCategory($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $category_id = $request->input('category_id', -1);
        $this->articleCategoryValidator->setId($category_id);
        $this->articleCategoryValidator->with($request->all())->passesOrFail('update');

        $data = $this->articleCategoryService->edit($category_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $category_id = $request->input('category_id', -1);
        $data = $this->articleCategoryService->removeCategory($category_id);

        return Respond::success($data);
    }

}
